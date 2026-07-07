<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeLeaveRequest;
use App\Models\Geofence;
use App\Models\ShiftRosterEntry;
use App\Services\leaverequestPrivatefunctions\LeaveRequestApproverResolver;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardService
{
    private const FINAL_APPROVAL_ACTION_TYPE = 2;

    private const DEPARTMENT_HEAD_ROLE_LEVEL = 3;

    public function __construct(
        private readonly ShiftRosterApprovalService $shiftRosterApprovalService,
        private readonly LeaveRequestApproverResolver $leaveRequestApproverResolver,
        private readonly EmployeeViewerScopeService $viewerScope,
        private readonly EmployeeWorkingScheduleService $employeeWorkingScheduleService,
        private readonly PublicHolidayResolver $publicHolidayResolver,
    ) {
    }

    public function index()
    {
        $geofences = Geofence::with('sbu')->orderBy('name')->get();
        $counterStats = $this->getCounterStats();

        $quotaWarningDays = 14;
        $quotaWarningThreshold = 20;

        $quotaWarnings = method_exists($this, 'getDepartmentalQuotaWarnings')
            ? $this->getDepartmentalQuotaWarnings(
                days: $quotaWarningDays,
                threshold: $quotaWarningThreshold
            )
            : [];

        return view('admin.dashboard.index', compact(
            'geofences',
            'counterStats',
            'quotaWarnings',
            'quotaWarningDays',
            'quotaWarningThreshold'
        ));
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, can_act_on_approvals: bool, is_human_resource_viewer: bool}
     */
    public function getPendingApprovals(): array
    {
        $viewer = Auth::user();
        $viewerEmployee = $viewer?->employee;

        if (! $viewerEmployee) {
            return [
                'items' => [],
                'can_act_on_approvals' => false,
                'is_human_resource_viewer' => false,
            ];
        }

        $viewerEmployee->loadMissing('department');
        $isHumanResource = $this->isHumanResourceDepartment($viewerEmployee->department);
        $isSystemAdmin = $viewer->isSystemAdminUser();

        $query = EmployeLeaveRequest::with([
                'fromEmployee:id,full_name,department_id',
                'fromEmployee.mediaFiles',
                'fromUser:id,name,employee_id',
                'fromUser.employee:id,full_name',
                'toEmployee:id,full_name',
                'leaveType:id,name',
            ])
            ->where('action_type', self::FINAL_APPROVAL_ACTION_TYPE)
            ->where('status', 0);

        if ($isHumanResource && ! $isSystemAdmin) {
            $this->scopePendingApprovalsByApplicantDepartment($query, $viewerEmployee);
        } else {
            $query->where('to_employee_id', $viewerEmployee->id);

            if (! $isSystemAdmin) {
                $this->scopePendingApprovalsByApplicantDepartment($query, $viewerEmployee);
            }
        }

        $requests = $query->orderByDesc('created_at')->get();

        $items = $requests->map(function ($r) use ($viewerEmployee, $isHumanResource, $isSystemAdmin) {
            $name     = optional($r->fromEmployee)->full_name ?? 'Unknown';
            $words    = explode(' ', trim($name));
            $initials = strtoupper(
                substr($words[0] ?? '', 0, 1) . substr($words[1] ?? '', 0, 1)
            );

            return [
                'id'           => $r->id,
                'name'         => $name,
                'initials'     => $initials,
                'avatar_url'   => $this->resolveEmployeeAvatarUrl($r->fromEmployee),
                'leave_type'   => optional($r->leaveType)->name ?? 'Leave',
                'requested_by' => optional($r->fromUser)->name
                    ?? optional($r->fromUser?->employee)->full_name
                    ?? 'Unknown',
                'request_date' => \Carbon\Carbon::parse($r->created_at)->format('M d, Y'),
                'start_date'   => \Carbon\Carbon::parse($r->start_date)->format('M d, Y'),
                'end_date'     => \Carbon\Carbon::parse($r->end_date)->format('M d, Y'),
                'reason'       => $r->reason ?? '',
                'can_act'      => $this->canActOnPendingApproval(
                    $r,
                    $viewerEmployee,
                    $isHumanResource,
                    $isSystemAdmin
                ),
                'requires_hr_delegation_confirm' => $this->requiresHrDelegationConfirm(
                    $r,
                    $viewerEmployee,
                    $isHumanResource
                ),
                'assigned_approver_name' => optional($r->toEmployee)->full_name ?? 'Unknown',
            ];
        })->values()->all();

        $canAct = collect($items)->contains(fn (array $item) => $item['can_act']);

        return [
            'items' => $items,
            'can_act_on_approvals' => $canAct,
            'is_human_resource_viewer' => $isHumanResource,
        ];
    }

    public function getPendingRosterApprovals(): array
    {
        $user = Auth::user();
        $viewerEmployee = $user?->employee;

        if (! $viewerEmployee) {
            return [];
        }

        return $this->shiftRosterApprovalService
            ->getPendingForDashboardViewer($viewerEmployee, $user)
            ->map(fn (array $item) => $this->shiftRosterApprovalService->formatPendingListItem(
                $item['request'],
                $item['segment'] ?? null
            ))
            ->values()
            ->all();
    }

    public function getUpcomingHolidays(int $days = 7): array
    {
        $today = Carbon::today();
        $end   = Carbon::today()->addDays($days);

        $holidays = PublicHoliday::with('organizations:id,name')
            ->where('is_blackout', false)
            ->where(function ($q) use ($today, $end) {
                // Non-recurring: starts within window OR already ongoing (started before today, ends after today)
                $q->where(function ($q2) use ($today, $end) {
                    $q2->where('is_recurring', false)
                        ->where(function ($q3) use ($today, $end) {
                            $q3->where(function ($q4) use ($today, $end) {
                                    $q4->where('start_date', '>=', $today)
                                       ->where('start_date', '<=', $end);
                                })
                                ->orWhere(function ($q4) use ($today) {
                                    $q4->where('start_date', '<', $today)
                                       ->where('end_date', '>=', $today);
                                });
                        });
                // Recurring: this year's occurrence falls within the window
                })->orWhere(function ($q2) use ($today, $end) {
                    $q2->where('is_recurring', true)
                        ->whereRaw(
                            "DATE(CONCAT(YEAR(CURDATE()), '-', MONTH(start_date), '-', DAY(start_date))) BETWEEN ? AND ?",
                            [$today->toDateString(), $end->toDateString()]
                        );
                });
            })
            ->orderByRaw("IF(is_recurring = 1,
                DATE(CONCAT(YEAR(CURDATE()), '-', MONTH(start_date), '-', DAY(start_date))),
                IF(start_date < CURDATE(), CURDATE(), start_date)) ASC")
            ->get();

        return $holidays->map(function ($h) {
            if ($h->is_recurring) {
                $effectiveDate = Carbon::createFromDate(now()->year, $h->start_date->month, $h->start_date->day);
            } elseif ($h->start_date->lt(Carbon::today())) {
                $effectiveDate = Carbon::today();
            } else {
                $effectiveDate = $h->start_date;
            }

            if ($h->organization_scope === 'specific') {
                $orgNames   = $h->organizations->pluck('name');
                $scopeLabel = $orgNames->isNotEmpty() ? $orgNames->implode(', ') : 'Specific Org';
                $badgeClass = 'bg-main';
            } else {
                $scopeLabel = 'All Organizations';
                $badgeClass = 'bg-info';
            }

            $isOngoing = !$h->is_recurring && $h->start_date->lt(Carbon::today());

            return [
                'id'          => $h->id,
                'name'        => $h->name,
                'day'         => $effectiveDate->format('d'),
                'month'       => $effectiveDate->format('M'),
                'type'        => $h->organization_scope === 'specific' ? 'Organization Holiday' : 'Public Holiday',
                'scope_label' => $scopeLabel,
                'badge_class' => $badgeClass,
                'is_ongoing'  => $isOngoing,
            ];
        })->values()->all();
    }

    public function getWhoIsOutToday(): array
    {
        $viewer = Auth::user();
        $viewerEmployee = $viewer?->employee;

        if (! $viewerEmployee) {
            return [];
        }

        $today = now()->toDateString();
        $requests = EmployeLeaveRequest::with([
                'fromEmployee:id,first_name,middle_name,last_name,full_name,roster_display_middle_name,department_id',
                'fromEmployee.mediaFiles',
                'leaveType:id,name,code',
            ])
            ->where('status', 3)
            ->whereIn('action_type', [0, 2])
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->when(
                ! $viewer->isSystemAdminUser(),
                fn ($query) => $this->scopePendingApprovalsByApplicantDepartment($query, $viewerEmployee)
            )
            ->get()
            ->unique(fn (EmployeLeaveRequest $request) => (int) $request->from_employee_id);

        return $requests->map(function ($r) {
            $employee = $r->fromEmployee;
            $name     = $employee?->full_name ?? 'Unknown';
            $displayName = $employee ? $employee->rosterDisplayName() : 'Unknown';

            $words = preg_split('/\s+/u', trim($displayName), -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $initials = strtoupper(
                substr($words[0] ?? '', 0, 1) . substr($words[1] ?? '', 0, 1)
            );

            $leaveTypeName = optional($r->leaveType)->name ?? 'Leave';
            $leaveTypeCode = trim((string) (optional($r->leaveType)->code ?? ''));

            $avatarUrl = $this->resolveEmployeeAvatarUrl($r->fromEmployee);

            return [
                'id'               => $r->id,
                'name'             => $name,
                'short_name'       => $displayName,
                'initials'         => $initials,
                'avatar_url'       => $avatarUrl,
                'leave_type'       => $leaveTypeName,
                'leave_type_short' => $leaveTypeCode !== '' ? $leaveTypeCode : $leaveTypeName,
                'status_dot'       => 'on-leave',
            ];
        })->values()->all();
    }

    public function getDepartmentDistributionData(): array
    {
        $viewer = Auth::user();
        $viewerEmployee = $viewer?->employee;

        if (! $viewerEmployee && ! $viewer?->isSystemAdminUser()) {
            return ['labels' => [], 'datasets' => []];
        }

        $departmentIds = $this->resolveDepartmentIdsForDistribution($viewerEmployee, $viewer);

        if ($departmentIds === []) {
            return ['labels' => [], 'datasets' => []];
        }

        $departments = Department::query()
            ->whereIn('id', $departmentIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $deptTotals = Employee::query()
            ->whereIn('department_id', $departmentIds)
            ->where('is_active', true)
            ->whereNull('deleted_at');
        $this->viewerScope->applySbuScopeToEmployeeQuery($deptTotals);
        $deptTotals = $deptTotals
            ->groupBy('department_id')
            ->selectRaw('department_id, COUNT(*) as total')
            ->pluck('total', 'department_id');

        $labels = [''];
        $weekStarts = [];

        for ($weekOffset = 5; $weekOffset >= 0; $weekOffset--) {
            $weekStarts[] = now()->startOfWeek(Carbon::MONDAY)->subWeeks($weekOffset);
            $labels[] = 'Week ' . (6 - $weekOffset);
        }

        $datasets = [];

        foreach ($departments as $department) {
            $departmentId = (int) $department->id;
            $deptTotal = (int) $deptTotals->get($departmentId, 0);

            if ($deptTotal === 0) {
                continue;
            }

            $series = [0];

            foreach ($weekStarts as $weekStart) {
                $series[] = $this->averageAvailablePercentForDepartmentWeek(
                    $departmentId,
                    $deptTotal,
                    $weekStart
                );
            }

            $datasets[] = [
                'label' => $department->name,
                'data' => $series,
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    public function getAttendanceChartData(int $days): array
    {
        $viewer = Auth::user();
        $viewerEmployee = $viewer?->employee;

        if (! $viewerEmployee && ! $viewer?->isSystemAdminUser()) {
            return ['labels' => [], 'present' => [], 'absent' => [], 'onLeave' => []];
        }

        $departmentIds = $this->resolveDepartmentIdsForDistribution($viewerEmployee, $viewer);

        if ($departmentIds === []) {
            return ['labels' => [], 'present' => [], 'absent' => [], 'onLeave' => []];
        }

        $totalEmployeesQuery = Employee::query()
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->whereIn('department_id', $departmentIds);
        $this->viewerScope->applySbuScopeToEmployeeQuery($totalEmployeesQuery);
        $totalEmployees = $totalEmployeesQuery->count();

        $labels  = [];
        $present = [];
        $absent  = [];
        $onLeave = [];

        if ($days === 14) {
            $startDate = now()->subWeek()->startOfWeek(Carbon::MONDAY);
        } else {
            $startDate = now()->startOfWeek(Carbon::MONDAY);
        }

        for ($i = 0; $i < $days; $i++) {
            $currentDate = $startDate->copy()->addDays($i);
            $date   = $currentDate->toDateString();
            
            if ($days === 14) {
                $label = $currentDate->format('D d');
            } else {
                $label = $currentDate->format('D');
            }

            $onLeaveCount = EmployeLeaveRequest::query()
                ->where('status', 3)
                ->whereIn('action_type', [0, 2])
                ->where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->whereHas('fromEmployee', function ($query) use ($departmentIds) {
                    $query
                        ->whereIn('department_id', $departmentIds)
                        ->where('is_active', true)
                        ->whereNull('deleted_at');
                    $this->viewerScope->applySbuScopeToEmployeeQuery($query);
                })
                ->distinct()
                ->count('from_employee_id');

            $presentCountQuery = DB::table('shift_rosters as sr')
                ->join('employees as e', 'e.id', '=', 'sr.employee_id')
                ->whereDate('sr.roster_date', $date)
                ->where('sr.status', 1)
                ->whereNull('sr.deleted_at')
                ->whereNull('e.deleted_at')
                ->where('e.is_active', true)
                ->whereIn('e.department_id', $departmentIds);
            $this->applySbuScopeToEmployeesDbTable($presentCountQuery, 'e');
            $presentCount = $presentCountQuery->count();

            $absentCountQuery = DB::table('shift_rosters as sr')
                ->join('employees as e', 'e.id', '=', 'sr.employee_id')
                ->whereDate('sr.roster_date', $date)
                ->where('sr.status', 3)
                ->whereNull('sr.deleted_at')
                ->whereNull('e.deleted_at')
                ->where('e.is_active', true)
                ->whereIn('e.department_id', $departmentIds);
            $this->applySbuScopeToEmployeesDbTable($absentCountQuery, 'e');
            $absentCount = $absentCountQuery->count();

            if ($presentCount === 0 && $absentCount === 0) {
                $presentCount = max(0, $totalEmployees - $onLeaveCount);
                $absentCount  = 0;
            }

            $labels[]  = $label;
            $present[] = $presentCount;
            $absent[]  = $absentCount;
            $onLeave[] = $onLeaveCount;
        }

        return compact('labels', 'present', 'absent', 'onLeave');
    }

    private function getCounterStats(): array
    {
        $today     = Carbon::today();
        $yesterday = Carbon::yesterday();

        // ── Total Employees (exclude terminated) ─────────────────────────
        $totalToday     = $this->baseDashboardEmployeeQuery()->count();
        $totalYesterday = $this->baseDashboardEmployeeQuery()
            ->whereDate('created_at', '<=', $yesterday->toDateString())
            ->count();
        $totalDelta = $this->percentDelta($totalToday, $totalYesterday);

        // ── Active / Workforce ────────────────────────────────────────────
        $activeEmployees = $this->baseDashboardEmployeeQuery()
            ->where('is_active', true)
            ->count();
        $workforcePercent = $totalToday > 0
            ? round(($activeEmployees / $totalToday) * 100)
            : 0;

        // ── Absent / On Leave (approved leaves covering today) ────────────
        $absentToday = $this->countOnLeaveEmployeesForDate($today);
        $absentYesterday = $this->countOnLeaveEmployeesForDate($yesterday);
        $absentDelta = $this->percentDelta($absentToday, $absentYesterday);

        // ── Present Today (working-day schedule minus leave/off/unassigned) ─
        $presentToday     = $this->countPresentEmployeesForDate($today);
        $presentYesterday = $this->countPresentEmployeesForDate($yesterday);
        $presentDelta = $this->percentDelta($presentToday, $presentYesterday);

        // ── Late Arrivals (shift roster late check-in flag) ───────────────
        $lateToday     = $this->countLateArrivalsForDate($today);
        $lateYesterday = $this->countLateArrivalsForDate($yesterday);
        $lateDelta = $this->percentDelta($lateToday, $lateYesterday);

        return [
            'totalEmployees'    => $totalToday,
            'totalDelta'        => $totalDelta,
            'presentToday'      => $presentToday,
            'presentDelta'      => $presentDelta,
            'absentOnLeave'     => $absentToday,
            'absentDelta'       => $absentDelta,
            'lateArrivals'      => $lateToday,
            'lateDelta'         => $lateDelta,
            'activeEmployees'   => $activeEmployees,
            'workforcePercent'  => $workforcePercent,
        ];
    }

    private function baseDashboardEmployeeQuery()
    {
        $query = Employee::query()
            ->excludeTerminated()
            ->whereNull('deleted_at');
        $this->viewerScope->applySbuScopeToEmployeeQuery($query);

        return $query;
    }

    private function countOnLeaveEmployeesForDate(Carbon $date): int
    {
        $dateStr = $date->toDateString();

        return EmployeLeaveRequest::query()
            ->where('status', 3)
            ->whereIn('action_type', [0, 2])
            ->where('start_date', '<=', $dateStr)
            ->where('end_date', '>=', $dateStr)
            ->whereHas('fromEmployee', function ($query) {
                $query->excludeTerminated()->whereNull('deleted_at');
                $this->viewerScope->applySbuScopeToEmployeeQuery($query);
            })
            ->distinct('from_employee_id')
            ->count('from_employee_id');
    }

    private function countPresentEmployeesForDate(Carbon $date): int
    {
        $dateStr = $date->toDateString();
        $employees = $this->baseDashboardEmployeeQuery()
            ->where('is_active', true)
            ->get(['id', 'organization_id', 'department_id', 'sbu_id', 'engagement_mode', 'working_days', 'hybrid_days', 'hybrid_offsite_days']);

        if ($employees->isEmpty()) {
            return 0;
        }

        $employeeIds = $employees->pluck('id');
        $onLeaveIds = EmployeLeaveRequest::query()
            ->where('status', 3)
            ->whereIn('action_type', [0, 2])
            ->where('start_date', '<=', $dateStr)
            ->where('end_date', '>=', $dateStr)
            ->whereIn('from_employee_id', $employeeIds)
            ->distinct()
            ->pluck('from_employee_id')
            ->flip();

        $rosterEntries = ShiftRosterEntry::query()
            ->whereIn('employee_id', $employeeIds)
            ->whereDate('roster_date', $dateStr)
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('employee_id');

        $holidays = $this->publicHolidayResolver->loadHolidaysForRange($date->copy(), $date->copy());

        $present = 0;
        foreach ($employees as $employee) {
            if ($this->employeeCountsAsPresentToday(
                $employee,
                $date,
                $rosterEntries->get($employee->id),
                $onLeaveIds->has($employee->id),
                $holidays
            )) {
                $present++;
            }
        }

        return $present;
    }

    private function employeeCountsAsPresentToday(
        Employee $employee,
        Carbon $date,
        ?ShiftRosterEntry $rosterEntry,
        bool $onLeave,
        Collection $holidays,
    ): bool {
        if ($onLeave) {
            return false;
        }

        if ($rosterEntry && strtolower(trim((string) $rosterEntry->status)) === 'off') {
            return false;
        }

        $holiday = $this->publicHolidayResolver->resolveForAssigneeOnDate(
            $holidays,
            $employee->organization_id ? (int) $employee->organization_id : null,
            $employee->department_id ? (int) $employee->department_id : null,
            $employee->sbu_id ? (int) $employee->sbu_id : null,
            $date->toDateString(),
        );

        $isShiftBased = $this->employeeWorkingScheduleService->isShiftBased($employee);
        $workingDays = $this->employeeWorkingScheduleService->resolveWorkingDays($employee);

        if ($holiday !== null) {
            return $isShiftBased
                && $rosterEntry !== null
                && $this->isRosterWorkShift($rosterEntry);
        }

        if ($isShiftBased) {
            return $rosterEntry !== null && $this->isRosterWorkShift($rosterEntry);
        }

        if ($this->employeeWorkingScheduleService->isWeeklyOffDay($date, $workingDays)) {
            return false;
        }

        return true;
    }

    private function isRosterWorkShift(ShiftRosterEntry $entry): bool
    {
        $status = strtolower(trim((string) $entry->status));

        if (in_array($status, ['off', 'cancelled', 'holiday', 'blackout'], true)) {
            return false;
        }

        if ($entry->is_custom_time) {
            return $entry->start_time !== null && $entry->end_time !== null;
        }

        return $entry->shift_planner_id !== null;
    }

    private function countLateArrivalsForDate(Carbon $date): int
    {
        return ShiftRosterEntry::query()
            ->whereDate('roster_date', $date->toDateString())
            ->where('late_check_in', true)
            ->whereNull('deleted_at')
            ->whereNotNull('employee_id')
            ->whereHas('employee', function ($query) {
                $query->excludeTerminated()->whereNull('deleted_at');
                $this->viewerScope->applySbuScopeToEmployeeQuery($query);
            })
            ->count();
    }

    /**
     * Returns a signed percentage delta string like "+5%" or "-3%" or "0%"
     */
    private function percentDelta(int $current, int $previous): string
    {
        if ($previous === 0) {
            return $current > 0 ? '+100%' : '0%';
        }
        $delta = round((($current - $previous) / $previous) * 100);
        return ($delta >= 0 ? '+' : '') . $delta . '%';
    }


    public function getDepartmentalQuotaWarnings(int $days = 14, int $threshold = 20): array
    {
        $viewer = Auth::user();
        $viewerEmployee = $viewer?->employee;

        if (! $viewerEmployee && ! $viewer?->isSystemAdminUser()) {
            return [];
        }

        $departmentIds = $this->resolveDepartmentIdsForDistribution($viewerEmployee, $viewer);

        if ($departmentIds === []) {
            return [];
        }

        $today = Carbon::today();
        $warnings = [];

        $deptTotalsQuery = DB::table('employees')
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->whereNotNull('department_id')
            ->whereIn('department_id', $departmentIds);
        $this->applySbuScopeToEmployeesDbTable($deptTotalsQuery);
        $deptTotals = $deptTotalsQuery
            ->select('department_id', DB::raw('COUNT(*) as total'))
            ->groupBy('department_id')
            ->pluck('total', 'department_id');

        if ($deptTotals->isEmpty()) {
            return [];
        }

        $leaveRequestTable = (new EmployeLeaveRequest())->getTable();

        for ($i = 1; $i <= $days; $i++) {
            $date = $today->copy()->addDays($i);
            $dateStr = $date->toDateString();

            $leaveCountsQuery = DB::table($leaveRequestTable . ' as lr')
                ->join('employees as e', 'e.id', '=', 'lr.from_employee_id')
                ->where('lr.status', 3)
                ->whereIn('lr.action_type', [0, 2])
                ->where('lr.start_date', '<=', $dateStr)
                ->where('lr.end_date', '>=', $dateStr)
                ->whereNull('e.deleted_at')
                ->whereIn('e.department_id', $departmentIds);
            $this->applySbuScopeToEmployeesDbTable($leaveCountsQuery, 'e');
            $leaveCounts = $leaveCountsQuery
                ->select('e.department_id', DB::raw('COUNT(DISTINCT lr.from_employee_id) as on_leave'))
                ->groupBy('e.department_id')
                ->pluck('on_leave', 'department_id');

            foreach ($leaveCounts as $deptId => $onLeave) {
                $total = $deptTotals->get($deptId, 0);
                if ($total === 0) {
                    continue;
                }

                $percent = round(($onLeave / $total) * 100);
                if ($percent < $threshold) {
                    continue;
                }

                $dateLabel = $date->isNextWeek()
                    ? 'next ' . $date->format('l') . ' (' . $date->format('M j') . ')'
                    : $date->format('D, M j');

                $color = $percent >= 40 ? 'danger' : 'warning';

                $warnings[] = [
                    'department_id' => $deptId,
                    'department_name' => null,
                    'date' => $dateStr,
                    'date_label' => $dateLabel,
                    'on_leave_count' => $onLeave,
                    'total_count' => $total,
                    'percent' => $percent,
                    'progress_color' => $color,
                    'badge_color' => $color,
                ];
            }
        }

        if (empty($warnings)) {
            return [];
        }

        $deptIds = array_unique(array_column($warnings, 'department_id'));
        $deptNames = DB::table('departments')
            ->whereIn('id', $deptIds)
            ->pluck('name', 'id');

        foreach ($warnings as &$warning) {
            $warning['department_name'] = $deptNames->get($warning['department_id'], 'Unknown Department');
        }
        unset($warning);

        usort($warnings, fn($a, $b) => $b['percent'] <=> $a['percent']);

        return array_slice($warnings, 0, 10);
    }

    private function canActOnPendingApproval(
        EmployeLeaveRequest $request,
        Employee $viewerEmployee,
        bool $isHumanResourceViewer,
        bool $isSystemAdmin
    ): bool {
        if ($this->canHrDelegateOnPendingApproval($request, $viewerEmployee, $isHumanResourceViewer)) {
            return true;
        }

        $isAssignedApprover = (int) $request->to_employee_id === (int) $viewerEmployee->id;

        if (! $isAssignedApprover) {
            return false;
        }

        if ($isSystemAdmin || ! $isHumanResourceViewer) {
            return true;
        }

        $viewerDepartmentId = $viewerEmployee->department_id ? (int) $viewerEmployee->department_id : null;
        $applicantDepartmentId = $request->fromEmployee?->department_id
            ? (int) $request->fromEmployee->department_id
            : null;

        return $viewerDepartmentId
            && $applicantDepartmentId
            && $viewerDepartmentId === $applicantDepartmentId;
    }

    private function requiresHrDelegationConfirm(
        EmployeLeaveRequest $request,
        Employee $viewerEmployee,
        bool $isHumanResourceViewer
    ): bool {
        return $this->canHrDelegateOnPendingApproval($request, $viewerEmployee, $isHumanResourceViewer);
    }

    private function canHrDelegateOnPendingApproval(
        EmployeLeaveRequest $request,
        Employee $viewerEmployee,
        bool $isHumanResourceViewer
    ): bool {
        if (! $isHumanResourceViewer || ! $this->isHrRoleLevelThreeViewer($viewerEmployee)) {
            return false;
        }

        if ((int) $request->action_type !== self::FINAL_APPROVAL_ACTION_TYPE) {
            return false;
        }

        if ((int) $request->status !== 0) {
            return false;
        }

        $viewerDepartmentId = $viewerEmployee->department_id ? (int) $viewerEmployee->department_id : null;
        $applicantDepartmentId = $request->fromEmployee?->department_id
            ? (int) $request->fromEmployee->department_id
            : null;

        if (! $viewerDepartmentId || ! $applicantDepartmentId || $viewerDepartmentId === $applicantDepartmentId) {
            return false;
        }

        $sbuId = $viewerEmployee->sbu_id ? (int) $viewerEmployee->sbu_id : null;

        if (! $sbuId) {
            return false;
        }

        return Department::query()
            ->where('id', $applicantDepartmentId)
            ->where('sbu_id', $sbuId)
            ->where('is_active', true)
            ->exists();
    }

    private function isHrRoleLevelThreeViewer(Employee $viewerEmployee): bool
    {
        $viewerEmployee->loadMissing('department');

        if (! $this->isHumanResourceDepartment($viewerEmployee->department)) {
            return false;
        }

        return $this->leaveRequestApproverResolver->resolveEmployeeRoleLevel($viewerEmployee) === self::DEPARTMENT_HEAD_ROLE_LEVEL;
    }

    /**
     * @return array<int, int>
     */
    private function resolveDepartmentIdsForDistribution(?Employee $viewerEmployee, $viewer): array
    {
        if ($viewer?->isSystemAdminUser()) {
            $adminDepartmentsQuery = Employee::query()
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->whereNotNull('department_id');
            $this->viewerScope->applySbuScopeToEmployeeQuery($adminDepartmentsQuery);

            return $adminDepartmentsQuery
                ->distinct()
                ->pluck('department_id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
        }

        if (! $viewerEmployee) {
            return [];
        }

        return $this->resolveViewerApplicantDepartmentIds($viewerEmployee);
    }

    private function averageAvailablePercentForDepartmentWeek(
        int $departmentId,
        int $deptTotal,
        Carbon $weekStart
    ): int {
        if ($deptTotal === 0) {
            return 0;
        }

        $today = Carbon::today();
        $dailyPercents = [];

        for ($dayOffset = 0; $dayOffset < 7; $dayOffset++) {
            $day = $weekStart->copy()->addDays($dayOffset);

            if ($day->gt($today)) {
                continue;
            }

            $date = $day->toDateString();
            $onLeave = EmployeLeaveRequest::query()
                ->where('status', 3)
                ->whereIn('action_type', [0, 2])
                ->where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->whereHas('fromEmployee', function ($query) use ($departmentId) {
                    $query
                        ->where('department_id', $departmentId)
                        ->where('is_active', true)
                        ->whereNull('deleted_at');
                    $this->viewerScope->applySbuScopeToEmployeeQuery($query);
                })
                ->distinct()
                ->count('from_employee_id');

            $available = max(0, $deptTotal - $onLeave);
            $dailyPercents[] = ($available / $deptTotal) * 100;
        }

        if ($dailyPercents === []) {
            return 0;
        }

        return (int) round(array_sum($dailyPercents) / count($dailyPercents));
    }

    private function scopePendingApprovalsByApplicantDepartment($query, Employee $viewerEmployee): void
    {
        $viewerEmployee->loadMissing('department');
        $departmentIds = $this->resolveViewerApplicantDepartmentIds($viewerEmployee);

        if ($departmentIds === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereHas(
            'fromEmployee',
            function ($employeeQuery) use ($departmentIds) {
                $employeeQuery->whereIn('department_id', $departmentIds);
                $this->viewerScope->applySbuScopeToEmployeeQuery($employeeQuery);
            }
        );
    }

    /**
     * @param  \Illuminate\Database\Query\Builder  $query
     */
    private function applySbuScopeToEmployeesDbTable($query, ?string $alias = null): void
    {
        $sbuId = $this->viewerScope->resolveViewerSbuId();

        if ($sbuId === null) {
            return;
        }

        if ($sbuId <= 0) {
            $query->whereRaw('1 = 0');

            return;
        }

        $column = $alias !== null ? "{$alias}.sbu_id" : 'sbu_id';
        $query->where($column, $sbuId);
    }

    /**
     * Departments whose applicants this viewer may see on dashboard widgets.
     * Uses the same rules as roster / balance tracker (primary department + department_ids JSON).
     *
     * @return array<int, int>
     */
    private function resolveViewerApplicantDepartmentIds(Employee $viewerEmployee): array
    {
        $departmentIds = $this->viewerScope->resolveViewerDepartmentIds();

        return $departmentIds ?? [];
    }

    private function isHumanResourceDepartment(?Department $department): bool
    {
        if (! $department) {
            return false;
        }

        $normalized = strtolower(trim((string) $department->name));

        return in_array($normalized, ['human resource', 'human resources'], true);
    }

    private function resolveEmployeeAvatarUrl(?Employee $employee): ?string
    {
        if (! $employee) {
            return null;
        }

        $employee->loadMissing('mediaFiles');
        $photo = $employee->mediaFiles->where('file_type', 'photo')->first();
        if ($photo && $photo->file_path) {
            return asset('storage/' . $photo->file_path);
        }

        return null;
    }
}
