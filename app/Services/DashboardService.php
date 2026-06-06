<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeLeaveRequest;
use App\Models\Geofence;
use App\Models\PublicHoliday;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardService
{
    public function __construct(
        private readonly ShiftRosterApprovalService $shiftRosterApprovalService
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

    public function getPendingApprovals(): array
    {
        $requests = EmployeLeaveRequest::with([
                'fromEmployee:id,full_name',
                'leaveType:id,name',
            ])
            ->where('status', 0)
            ->orderByDesc('created_at')
            ->get();

        return $requests->map(function ($r) {
            $name     = optional($r->fromEmployee)->full_name ?? 'Unknown';
            $words    = explode(' ', trim($name));
            $initials = strtoupper(
                substr($words[0] ?? '', 0, 1) . substr($words[1] ?? '', 0, 1)
            );

            return [
                'id'           => $r->id,
                'name'         => $name,
                'initials'     => $initials,
                'leave_type'   => optional($r->leaveType)->name ?? 'Leave',
                'request_date' => \Carbon\Carbon::parse($r->created_at)->format('M d, Y'),
                'start_date'   => \Carbon\Carbon::parse($r->start_date)->format('M d, Y'),
                'end_date'     => \Carbon\Carbon::parse($r->end_date)->format('M d, Y'),
                'reason'       => $r->reason ?? '',
            ];
        })->values()->all();
    }

    public function getPendingRosterApprovals(): array
    {
        $user = Auth::user();
        if (! $user || ! $user->employee_id) {
            return [];
        }

        return $this->shiftRosterApprovalService
            ->getPendingForApprover((int) $user->employee_id)
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
        $today = now()->toDateString();
        $requests = EmployeLeaveRequest::with([
                'fromEmployee:id,full_name',
                'leaveType:id,name',
            ])
            ->where('status', 3) // Approved
            ->whereIn('action_type', [0, 2]) // Leave or Duty Off
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->get();

        return $requests->map(function ($r) {
            $name     = optional($r->fromEmployee)->full_name ?? 'Unknown';
            $words    = explode(' ', trim($name));
            $initials = strtoupper(
                substr($words[0] ?? '', 0, 1) . substr($words[1] ?? '', 0, 1)
            );
            
            // Format name as "First Name Last Initial."
            $shortName = $words[0] ?? '';
            if (isset($words[1])) {
                $shortName .= ' ' . substr($words[1], 0, 1) . '.';
            }

            return [
                'id'           => $r->id,
                'name'         => $name,
                'short_name'   => $shortName,
                'initials'     => $initials,
                'leave_type'   => optional($r->leaveType)->name ?? 'Leave',
                'status_dot'   => 'on-leave', 
            ];
        })->values()->all();
    }

    public function getAttendanceChartData(int $days): array
    {
        $totalEmployees = Employee::where('is_active', true)->whereNull('deleted_at')->count();
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

            $onLeaveCount = EmployeLeaveRequest::where('status', 3)
                ->whereIn('action_type', [0, 2])
                ->where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->distinct('from_employee_id')
                ->count('from_employee_id');

            $presentCount = DB::table('shift_rosters')
                ->whereDate('roster_date', $date)
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->count();

            $absentCount = DB::table('shift_rosters')
                ->whereDate('roster_date', $date)
                ->where('status', 3)
                ->whereNull('deleted_at')
                ->count();

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
        $today     = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // ── Total Employees ──────────────────────────────────────────────
        $totalToday     = Employee::whereNull('deleted_at')->count();
        $totalYesterday = Employee::whereNull('deleted_at')
            ->whereDate('created_at', '<=', $yesterday)
            ->count();
        $totalDelta     = $this->percentDelta($totalToday, $totalYesterday);

        // ── Active / Workforce ────────────────────────────────────────────
        $activeEmployees   = Employee::where('is_active', true)->whereNull('deleted_at')->count();
        $workforcePercent  = $totalToday > 0
            ? round(($activeEmployees / $totalToday) * 100)
            : 0;

        // ── Absent / On Leave (approved leaves covering today) ────────────
        $absentToday = EmployeLeaveRequest::where('status', 3)
            ->whereIn('action_type', [0, 2])
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->distinct('from_employee_id')
            ->count('from_employee_id');

        $absentYesterday = EmployeLeaveRequest::where('status', 3)
            ->whereIn('action_type', [0, 2])
            ->where('start_date', '<=', $yesterday)
            ->where('end_date', '>=', $yesterday)
            ->distinct('from_employee_id')
            ->count('from_employee_id');

        $absentDelta = $this->percentDelta($absentToday, $absentYesterday);

        // ── Present Today (total − on-leave fallback) ─────────────────────
        $rosterPresent = DB::table('shift_rosters')
            ->whereDate('roster_date', $today)
            ->where('status', 1)       // 1 = present
            ->whereNull('deleted_at')
            ->count();

        $rosterYesterdayPresent = DB::table('shift_rosters')
            ->whereDate('roster_date', $yesterday)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->count();

        // Use roster data if available, otherwise fall back to total − absent
        $presentToday     = $rosterPresent > 0
            ? $rosterPresent
            : max(0, $totalToday - $absentToday);

        $presentYesterday = $rosterYesterdayPresent > 0
            ? $rosterYesterdayPresent
            : max(0, $totalYesterday - $absentYesterday);

        $presentDelta = $this->percentDelta($presentToday, $presentYesterday);

        // ── Late Arrivals (from shift_rosters status=2 for late) ──────────
        $lateToday = DB::table('shift_rosters')
            ->whereDate('roster_date', $today)
            ->where('status', 2)       // 2 = late
            ->whereNull('deleted_at')
            ->count();

        $lateYesterday = DB::table('shift_rosters')
            ->whereDate('roster_date', $yesterday)
            ->where('status', 2)
            ->whereNull('deleted_at')
            ->count();

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
        $today = Carbon::today();
        $warnings = [];

        $deptTotals = DB::table('employees')
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->whereNotNull('department_id')
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

            $leaveCounts = DB::table($leaveRequestTable . ' as lr')
                ->join('employees as e', 'e.id', '=', 'lr.from_employee_id')
                ->where('lr.status', 3)
                ->whereIn('lr.action_type', [0, 2])
                ->where('lr.start_date', '<=', $dateStr)
                ->where('lr.end_date', '>=', $dateStr)
                ->whereNull('e.deleted_at')
                ->whereNotNull('e.department_id')
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
}
