<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeLeaveEntity;
use App\Models\EmployeeWorkAssignment;
use App\Models\LeaveType;
use App\Models\ShiftRosterEntry;
use App\Models\Sbu;
use App\Models\Department;
use App\Models\SbuFloor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MonthlySummaryService
{
    private const PUBLIC_HOLIDAY_LABEL = 'Public Holiday';

    public function __construct(
        private readonly PublicHolidayResolver $publicHolidayResolver,
        private readonly EmployeeWorkingScheduleService $employeeWorkingScheduleService,
        private readonly EmployeeViewerScopeService $viewerScope,
    ) {}

    public function index(Request $request)
    {
        $filters = $this->resolveSummaryFilters($request);
        $month = $filters['month'];

        $sbus = $this->viewerScope->filterSbus(Sbu::query()->orderBy('name')->get());
        $departments = $this->viewerScope->filterDepartments(Department::query()->orderBy('name')->get());

        $floorsForFilter = $this->viewerScope->filterFloors(
            SbuFloor::query()
                ->where('is_active', true)
                ->orderBy('sbu_id')
                ->orderBy('floor_number')
                ->orderBy('name')
                ->get(['id', 'sbu_id', 'name', 'floor_number'])
        )
            ->map(fn($f) => [
                'id' => $f->id,
                'sbu_id' => $f->sbu_id,
                'name' => $f->name,
                'floor_number' => $f->floor_number,
            ])
            ->values()
            ->all();

        $employees = $this->fetchEmployeesForSummary($filters);

        $tableLeaveTypes = $this->resolveTableLeaveTypes(
            $filters['sbu_id'],
            $employees->pluck('sbu_id')->filter()->map(fn($id) => (int) $id)->unique()->values()->all(),
        );

        $monthlySummary = $employees->map(function ($employee) use ($month, $tableLeaveTypes) {
            return $this->buildEmployeeMonthlySummary($employee, $month, $tableLeaveTypes);
        })->values();

        $monthlySummaryLeaveTypes = $tableLeaveTypes
            ->map(static fn(LeaveType $leaveType) => [
                'id' => $leaveType->id,
                'name' => $leaveType->name,
                'code' => $leaveType->code,
            ])
            ->values()
            ->all();

        $viewerEmployeeScope = $this->viewerScope->frontendScopePayload();

        return view('admin.monthly-summary.index', compact(
            'monthlySummary',
            'tableLeaveTypes',
            'monthlySummaryLeaveTypes',
            'sbus',
            'departments',
            'month',
            'floorsForFilter',
            'viewerEmployeeScope',
        ));
    }

    public function buildExportReport(Request $request): array
    {
        $filters = $this->resolveSummaryFilters($request);
        $month = $filters['month'];
        $period = Carbon::parse($month . '-01')->startOfMonth();

        $employees = $this->fetchEmployeesForSummary($filters);
        $tableLeaveTypes = $this->resolveTableLeaveTypes(
            $filters['sbu_id'],
            $employees->pluck('sbu_id')->filter()->map(fn($id) => (int) $id)->unique()->values()->all(),
        );

        $rows = $employees
            ->map(fn(Employee $employee) => $this->buildEmployeeMonthlySummary($employee, $month, $tableLeaveTypes))
            ->sortBy('employee_name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();

        $totalPresent = (int) array_sum(array_column($rows, 'present'));
        $totalAbsent = (int) array_sum(array_column($rows, 'absent'));
        $totalHalfDays = (int) array_sum(array_column($rows, 'half_days'));
        $employeeCount = count($rows);
        $totalScheduledDays = $employeeCount * $period->daysInMonth;
        $attendancePercentage = $totalScheduledDays > 0
            ? round(($totalPresent / $totalScheduledDays) * 100, 1)
            : 0.0;

        return [
            'organization_name' => config('app.name', 'Enaara Systems'),
            'report_title' => 'Monthly Summary Report',
            'report_subtitle' => 'Pre-Payroll Attendance & Leave Report',
            'period_label' => $period->format('F Y'),
            'period_slug' => $month,
            'generated_at' => now()->format('d M Y, h:i A'),
            'filter_labels' => $this->resolveSummaryFilterLabels($filters),
            'leave_types' => $tableLeaveTypes
                ->map(static fn(LeaveType $leaveType) => [
                    'id' => $leaveType->id,
                    'name' => $leaveType->name,
                    'code' => $leaveType->code,
                ])
                ->values()
                ->all(),
            'employees' => $rows,
            'stats' => [
                'total_employees' => $employeeCount,
                'total_present' => $totalPresent,
                'total_absent' => $totalAbsent,
                'total_half_days' => $totalHalfDays,
                'attendance_percentage' => $attendancePercentage,
            ],
        ];
    }

    public function getEmployeeMonthlyCalendar(int $employeeId, string $month): array
    {
        $this->viewerScope->assertEmployeeIdAccessible($employeeId);

        $employee = Employee::with(['department', 'sbu', 'organization'])->findOrFail($employeeId);
        $attendance = $this->computeEmployeeMonthlyAttendance($employee, $month);

        return [
            'employee_id' => $employee->id,
            'month' => $month,
            'engagement_mode' => $employee->engagement_mode,
            'is_shift_based' => $this->employeeWorkingScheduleService->isShiftBased($employee),
            'days' => $attendance['days'],
            'stats' => $attendance['stats'],
        ];
    }

    public function buildEmployeeExportReport(int $employeeId, string $month): array
    {
        $this->viewerScope->assertEmployeeIdAccessible($employeeId);

        $employee = Employee::with([
            'department',
            'sbu',
            'assignedFloors' => static fn($q) => $q->where('is_active', true)->orderBy('floor_number'),
            'leaveQuotas' => function ($query) use ($month) {
                $query->where('year', Carbon::parse($month . '-01')->year)
                    ->with('leaveType');
            },
        ])->findOrFail($employeeId);

        $tableLeaveTypes = $this->resolveTableLeaveTypes(
            $employee->sbu_id ? (int) $employee->sbu_id : null,
            $employee->sbu_id ? [(int) $employee->sbu_id] : [],
        );

        $summary = $this->buildEmployeeMonthlySummary($employee, $month, $tableLeaveTypes);
        $calendar = $this->getEmployeeMonthlyCalendar($employeeId, $month);
        $period = Carbon::parse($month . '-01')->startOfMonth();
        $stats = $calendar['stats'];
        $totalDays = $period->daysInMonth;
        $attendancePercentage = $totalDays > 0
            ? round((($stats['present'] ?? 0) / $totalDays) * 100, 1)
            : 0.0;

        return [
            'organization_name' => config('app.name', 'Enaara Systems'),
            'report_title' => 'Employee Monthly Report',
            'report_subtitle' => 'Attendance & Leave Summary',
            'period_label' => $period->format('F Y'),
            'period_slug' => $month,
            'generated_at' => now()->format('d M Y, h:i A'),
            'employee' => $summary,
            'days' => $calendar['days'],
            'calendar_weeks' => $this->buildCalendarWeeks($calendar['days'], $month),
            'stats' => array_merge($stats, [
                'total_days' => $totalDays,
                'attendance_percentage' => $attendancePercentage,
            ]),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $days
     * @return array<int, array<int, array<string, mixed>|null>>
     */
    private function buildCalendarWeeks(array $days, string $month): array
    {
        $firstDay = Carbon::parse($month . '-01')->startOfMonth();
        $daysByNumber = collect($days)->keyBy('day');
        $cells = [];

        for ($i = 0; $i < (int) $firstDay->format('w'); $i++) {
            $cells[] = null;
        }

        for ($day = 1; $day <= $firstDay->daysInMonth; $day++) {
            $cells[] = $daysByNumber->get($day);
        }

        while (count($cells) % 7 !== 0) {
            $cells[] = null;
        }

        return array_chunk($cells, 7);
    }

    // old monthly attendance computation based on roster, leave and work assignment data
    // private function computeEmployeeMonthlyAttendance(Employee $employee, string $month): array
    // {
    //     $startDate = Carbon::parse($month . '-01')->startOfMonth();
    //     $endDate = $startDate->copy()->endOfMonth();

    //     $holidays = $this->publicHolidayResolver->loadHolidaysForRange($startDate, $endDate);

    //     $rosterEntries = ShiftRosterEntry::query()
    //         ->where('employee_id', $employee->id)
    //         ->whereBetween('roster_date', [$startDate->toDateString(), $endDate->toDateString()])
    //         ->get()
    //         ->keyBy(static fn (ShiftRosterEntry $entry) => $entry->roster_date->toDateString());

    //     $leaveEntities = EmployeLeaveEntity::query()
    //         ->where('employee_id', $employee->id)
    //         ->whereBetween('leave_date', [$startDate->toDateString(), $endDate->toDateString()])
    //         ->whereIn('status', [0, 1])
    //         ->with([
    //             'leaveType:id,name,code',
    //             'leaveRequest:id,is_outstation_leave,is_half_day,half_day_session,leave_type_id',
    //         ])
    //         ->get()
    //         ->keyBy(static fn (EmployeLeaveEntity $entity) => $entity->leave_date->toDateString());

    //     $workAssignments = EmployeeWorkAssignment::query()
    //         ->where('employee_id', $employee->id)
    //         ->whereBetween('assignment_date', [$startDate->toDateString(), $endDate->toDateString()])
    //         ->get()
    //         ->keyBy(static fn (EmployeeWorkAssignment $assignment) => $assignment->assignment_date->toDateString());

    //     $workingDays = $this->employeeWorkingScheduleService->resolveWorkingDays($employee);
    //     $isShiftBased = $this->employeeWorkingScheduleService->isShiftBased($employee);

    //     $days = [];
    //     $stats = [
    //         'present' => 0,
    //         'absent' => 0,
    //         'half_days' => 0,
    //         'leave' => 0,
    //         'off' => 0,
    //         'holiday' => 0,
    //         'late' => 0,
    //     ];

    //     $cursor = $startDate->copy();
    //     while ($cursor->lte($endDate)) {
    //         $dateStr = $cursor->toDateString();
    //         $day = $this->resolveDayStatus(
    //             $employee,
    //             $cursor,
    //             $isShiftBased,
    //             $workingDays,
    //             $holidays,
    //             $rosterEntries->get($dateStr),
    //             $leaveEntities->get($dateStr),
    //         );

    //         $day = $this->applyFutureDayContext($day, $cursor);
    //         $day = $this->applyWorkAssignmentOverlay($day, $workAssignments->get($dateStr), $isShiftBased);
    //         $day['is_absent_markable'] = $this->isAbsentMarkable($day, $cursor);

    //         $days[] = $day;
    //         $this->incrementCalendarStat($stats, $day);

    //         $cursor->addDay();
    //     }

    //     return [
    //         'days' => $days,
    //         'stats' => $stats,
    //     ];
    // }

    //new monthly attendance computation based on roster, leave and work assignment data 

    private function computeEmployeeMonthlyAttendance(Employee $employee, string $month): array
    {
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $holidays = $this->publicHolidayResolver->loadHolidaysForRange($startDate, $endDate);

        $rosterEntries = ShiftRosterEntry::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('roster_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(static fn(ShiftRosterEntry $entry) => $entry->roster_date->toDateString());

        $leaveEntitiesCollection = EmployeLeaveEntity::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('leave_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereIn('status', [0, 1])
            ->with([
                'leaveType:id,name,code',
                'leaveRequest:id,is_outstation_leave,is_half_day,half_day_session,leave_type_id',
            ])
            ->get();

        $leaveUsage = [];
        foreach ($leaveEntitiesCollection as $leaveEntity) {
            $resolved = RosterLeaveCellResolver::fromEntity($leaveEntity);
            if ($resolved['isWeeklyRest']) {
                continue;
            }

            $leaveTypeId = (int) ($leaveEntity->leave_type_id
                ?: $leaveEntity->leaveRequest?->leave_type_id
                ?: 0);

            if ($leaveTypeId <= 0) {
                continue;
            }

            $key = (string) $leaveTypeId;
            $leaveUsage[$key] = ($leaveUsage[$key] ?? 0) + (float) $leaveEntity->duration;
        }

        $leaveEntities = $leaveEntitiesCollection
            ->keyBy(static fn(EmployeLeaveEntity $entity) => $entity->leave_date->toDateString());

        $workAssignments = EmployeeWorkAssignment::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('assignment_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(static fn(EmployeeWorkAssignment $assignment) => $assignment->assignment_date->toDateString());

        $workingDays = $this->employeeWorkingScheduleService->resolveWorkingDays($employee);
        $isShiftBased = $this->employeeWorkingScheduleService->isShiftBased($employee);

        $days = [];
        $stats = [
            'present' => 0,
            'absent' => 0,
            'half_days' => 0,
            'leave' => 0,
            'off' => 0,
            'holiday' => 0,
            'late' => 0,
        ];

        $cursor = $startDate->copy();
        while ($cursor->lte($endDate)) {
            $dateStr = $cursor->toDateString();
            $day = $this->resolveDayStatus(
                $employee,
                $cursor,
                $isShiftBased,
                $workingDays,
                $holidays,
                $rosterEntries->get($dateStr),
                $leaveEntities->get($dateStr),
            );

            $day = $this->applyFutureDayContext($day, $cursor);
            $day = $this->applyWorkAssignmentOverlay($day, $workAssignments->get($dateStr), $isShiftBased);
            $day['is_absent_markable'] = $this->isAbsentMarkable($day, $cursor);

            $days[] = $day;
            $this->incrementCalendarStat($stats, $day);

            $cursor->addDay();
        }

        return [
            'days' => $days,
            'stats' => $stats,
            'leave_usage' => $leaveUsage,
        ];
    }



    private function resolveDayStatus(
        Employee $employee,
        Carbon $date,
        bool $isShiftBased,
        ?array $workingDays,
        Collection $holidays,
        ?ShiftRosterEntry $rosterEntry,
        ?EmployeLeaveEntity $leaveEntity,
    ): array {
        $dateStr = $date->toDateString();

        $holiday = $this->publicHolidayResolver->resolveForAssigneeOnDate(
            $holidays,
            $employee->organization_id ? (int) $employee->organization_id : null,
            $employee->department_id ? (int) $employee->department_id : null,
            $employee->sbu_id ? (int) $employee->sbu_id : null,
            $dateStr,
        );

        if ($leaveEntity) {
            $resolved = RosterLeaveCellResolver::fromEntity($leaveEntity);
            $duration = (float) $leaveEntity->duration;
            $session = $leaveEntity->half_day_session
                ? strtoupper((string) $leaveEntity->half_day_session)
                : null;

            if ($resolved['isWeeklyRest']) {
                return [
                    'date' => $dateStr,
                    'day' => (int) $date->day,
                    'status' => 'weekly_rest',
                    'label' => 'Weekly Rest',
                    'detail' => 'Outstation travel exempt',
                    'leave_type' => $resolved['leaveName'],
                    'duration' => $duration,
                ];
            }

            $isHalfDay = $resolved['isHalfDayLeave'];
            $leaveTypeName = $resolved['leaveName'];

            return [
                'date' => $dateStr,
                'day' => (int) $date->day,
                'status' => $isHalfDay ? 'half-day' : 'leave',
                'label' => $isHalfDay ? 'Half-day' : 'Leave',
                'detail' => $isHalfDay
                    ? trim($leaveTypeName . ($session ? " ({$session})" : ''))
                    : $leaveTypeName,
                'leave_type' => $leaveTypeName,
                'duration' => $duration,
            ];
        }

        if ($isShiftBased && $rosterEntry !== null && $this->isRosterWorkShift($rosterEntry)) {
            $day = $this->resolveShiftBasedDay($date, $rosterEntry, $workingDays);

            if ($holiday !== null) {
                return array_merge($day, [
                    'status' => 'present',
                    'label' => 'Present',
                    'detail' => self::PUBLIC_HOLIDAY_LABEL . ' (working)',
                    'is_holiday_work' => true,
                ]);
            }

            return $day;
        }

        if ($holiday !== null) {
            return [
                'date' => $dateStr,
                'day' => (int) $date->day,
                'status' => 'holiday',
                'label' => self::PUBLIC_HOLIDAY_LABEL,
                'detail' => self::PUBLIC_HOLIDAY_LABEL,
            ];
        }

        if ($isShiftBased) {
            return $this->resolveShiftBasedDay($date, $rosterEntry, $workingDays);
        }

        if ($this->employeeWorkingScheduleService->isHybrid($employee)) {
            return $this->resolveHybridDay($employee, $date, $workingDays);
        }

        return $this->resolveStandardDay($date, $workingDays);
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

    private function resolveShiftBasedDay(Carbon $date, ?ShiftRosterEntry $rosterEntry, ?array $workingDays): array
    {
        $base = [
            'date' => $date->toDateString(),
            'day' => (int) $date->day,
        ];

        if ($rosterEntry) {
            $status = strtolower(trim((string) $rosterEntry->status));

            if ($status === 'off') {
                return array_merge($base, [
                    'status' => 'off',
                    'label' => 'Off',
                    'detail' => 'Scheduled off day',
                ]);
            }

            return array_merge($base, [
                'status' => 'present',
                'label' => 'Present',
                'detail' => 'Working day',
            ]);
        }

        if ($this->employeeWorkingScheduleService->isWeeklyOffDay($date, $workingDays)) {
            return array_merge($base, [
                'status' => 'off',
                'label' => 'Off',
                'detail' => 'Weekly off',
            ]);
        }

        return array_merge($base, [
            'status' => 'present',
            'label' => 'Present',
            'detail' => 'Working day',
        ]);
    }

    private function resolveHybridDay(Employee $employee, Carbon $date, ?array $workingDays): array
    {
        $base = [
            'date' => $date->toDateString(),
            'day' => (int) $date->day,
        ];

        if ($this->employeeWorkingScheduleService->isWeeklyOffDay($date, $workingDays)) {
            return array_merge($base, [
                'status' => 'off',
                'label' => 'Off',
                'detail' => 'Weekly off',
            ]);
        }

        if ($this->employeeWorkingScheduleService->isHybridOffsiteDay($date, $employee)) {
            return array_merge($base, [
                'status' => 'work-from-home',
                'label' => 'WFH',
                'detail' => 'Scheduled off-site day',
            ]);
        }

        return array_merge($base, [
            'status' => 'present',
            'label' => 'Present',
            'detail' => 'Scheduled on-site day',
        ]);
    }

    private function resolveStandardDay(Carbon $date, ?array $workingDays): array
    {
        $base = [
            'date' => $date->toDateString(),
            'day' => (int) $date->day,
        ];

        if ($this->employeeWorkingScheduleService->isWeeklyOffDay($date, $workingDays)) {
            return array_merge($base, [
                'status' => 'off',
                'label' => 'Off',
                'detail' => 'Weekly off',
            ]);
        }

        return array_merge($base, [
            'status' => 'present',
            'label' => 'Present',
            'detail' => 'Working day',
        ]);
    }

    public function saveEmployeeWorkAssignment(
        int $employeeId,
        string $date,
        string $workType,
        ?string $notes = null,
    ): array {
        $this->viewerScope->assertEmployeeIdAccessible($employeeId);

        $employee = Employee::with(['department', 'sbu', 'organization'])->findOrFail($employeeId);
        $month = Carbon::parse($date)->format('Y-m');
        $attendance = $this->computeEmployeeMonthlyAttendance($employee, $month);
        $day = collect($attendance['days'])->firstWhere('date', Carbon::parse($date)->toDateString());

        if ($day === null) {
            throw new \InvalidArgumentException('Invalid assignment date.');
        }

        if ($workType !== 'none' && empty($day['is_assignable'])) {
            throw new \InvalidArgumentException(
                $day['assignment_blocked_message'] ?? 'Work location cannot be assigned on this day.'
            );
        }

        if ($workType === 'none') {
            EmployeeWorkAssignment::query()
                ->where('employee_id', $employeeId)
                ->whereDate('assignment_date', $date)
                ->delete();

            return ['cleared' => true];
        }

        if (! in_array($workType, [
            EmployeeWorkAssignment::TYPE_WORK_FROM_HOME,
            EmployeeWorkAssignment::TYPE_OUTSTATION,
            EmployeeWorkAssignment::TYPE_ABSENT,
        ], true)) {
            throw new \InvalidArgumentException('Invalid work type.');
        }

        if ($workType === EmployeeWorkAssignment::TYPE_ABSENT && empty($day['is_absent_markable'])) {
            throw new \InvalidArgumentException('Absent can only be marked on eligible working days.');
        }

        $assignment = EmployeeWorkAssignment::query()->firstOrNew([
            'employee_id' => $employeeId,
            'assignment_date' => $date,
        ]);

        $assignment->work_type = $workType;
        $assignment->notes = $notes !== null && trim($notes) !== '' ? trim($notes) : null;

        if (! $assignment->exists) {
            $assignment->created_by = auth()->id();
        }

        $assignment->updated_by = auth()->id();
        $assignment->save();

        return [
            'assignment' => [
                'id' => $assignment->id,
                'employee_id' => $assignment->employee_id,
                'assignment_date' => $assignment->assignment_date->toDateString(),
                'work_type' => $assignment->work_type,
                'notes' => $assignment->notes,
            ],
        ];
    }

    private function applyFutureDayContext(array $day, Carbon $date): array
    {
        if ($date->isAfter(Carbon::today()) && ($day['status'] ?? null) === 'present') {
            return array_merge($day, [
                'status' => 'scheduled',
                'label' => '',
                'detail' => '',
            ]);
        }

        return $day;
    }

    private function applyWorkAssignmentOverlay(
        array $day,
        ?EmployeeWorkAssignment $assignment,
        bool $isShiftBased,
    ): array {
        $day['base_status'] = $day['status'];
        $day['is_assignable'] = $this->isWorkLocationAssignable($day['status'], $isShiftBased);

        if (! $day['is_assignable']) {
            $day['assignment_block_reason'] = $this->resolveAssignmentBlockReason($day['status'], $isShiftBased);
            $day['assignment_blocked_message'] = $this->buildWorkAssignmentBlockedMessage(
                $day['assignment_block_reason']
            );
        }

        if ($assignment === null || ! $this->supportsWorkAssignmentOverlay($day['status'], $isShiftBased)) {
            return $day;
        }

        if ($assignment->work_type === EmployeeWorkAssignment::TYPE_ABSENT) {
            return array_merge($day, [
                'status' => 'absent',
                'label' => 'Absent',
                'detail' => 'Marked absent',
                'notes' => $assignment->notes,
                'work_assignment_id' => $assignment->id,
                'work_type' => $assignment->work_type,
            ]);
        }

        if ($assignment->work_type === EmployeeWorkAssignment::TYPE_WORK_FROM_HOME) {
            return array_merge($day, [
                'status' => 'work-from-home',
                'label' => 'Work from home',
                'detail' => 'Work from home',
                'notes' => $assignment->notes,
                'work_assignment_id' => $assignment->id,
                'work_type' => $assignment->work_type,
            ]);
        }

        if ($assignment->work_type === EmployeeWorkAssignment::TYPE_OUTSTATION) {
            return array_merge($day, [
                'status' => 'outstation',
                'label' => 'Outstation',
                'detail' => 'Outstation',
                'notes' => $assignment->notes,
                'work_assignment_id' => $assignment->id,
                'work_type' => $assignment->work_type,
            ]);
        }

        return $day;
    }

    private function isWorkLocationAssignable(string $status, bool $isShiftBased): bool
    {
        if (in_array($status, ['leave', 'half-day'], true)) {
            return false;
        }

        if (in_array($status, ['present', 'scheduled', 'work-from-home', 'outstation', 'absent'], true)) {
            return true;
        }

        if ($isShiftBased) {
            return false;
        }

        return in_array($status, ['off', 'holiday'], true);
    }

    private function supportsWorkAssignmentOverlay(string $status, bool $isShiftBased): bool
    {
        return $this->isWorkLocationAssignable($status, $isShiftBased);
    }

    private function resolveAssignmentBlockReason(string $status, bool $isShiftBased): ?string
    {
        if (in_array($status, ['leave', 'half-day'], true)) {
            return 'leave';
        }

        if ($isShiftBased && in_array($status, ['off', 'holiday'], true)) {
            return 'shift_planner';
        }

        return 'blocked';
    }

    private function buildWorkAssignmentBlockedMessage(?string $reason): string
    {
        return match ($reason) {
            'shift_planner' => 'This employee uses shift-based scheduling. Update off days and holidays from Shift Planner.',
            'leave' => 'Work location cannot be assigned on leave days.',
            default => 'Work location cannot be assigned on this day.',
        };
    }

    private function incrementCalendarStat(array &$stats, array $day): void
    {
        match ($day['status']) {
            'present', 'work-from-home', 'outstation' => $stats['present']++,
            'absent' => $stats['absent']++,
            'half-day' => $stats['half_days']++,
            'leave' => $stats['leave']++,
            'off' => $stats['off']++,
            'holiday' => $stats['holiday']++,
            default => null,
        };
    }

    private function isAbsentMarkable(array $day, Carbon $date): bool
    {
        if ($date->isAfter(Carbon::today())) {
            return false;
        }

        if (($day['status'] ?? null) === 'scheduled') {
            return false;
        }

        $baseStatus = $day['base_status'] ?? ($day['status'] ?? null);

        if (in_array($baseStatus, ['leave', 'half-day', 'off', 'holiday', 'scheduled'], true)) {
            return false;
        }

        if (($day['status'] ?? null) === 'absent') {
            return true;
        }

        return in_array($baseStatus, ['present', 'work-from-home', 'outstation'], true);
    }

    /**
     * @param  array<int>  $employeeSbuIds
     * @return Collection<int, LeaveType>
     */
    private function resolveSummaryFilters(Request $request): array
    {
        return [
            'month' => $request->get('month', now()->format('Y-m')),
            'sbu_id' => $request->filled('sbu_id') ? (int) $request->get('sbu_id') : null,
            'department_id' => $request->filled('department_id') ? (int) $request->get('department_id') : null,
            'floor_id' => $request->filled('floor_id') ? (int) $request->get('floor_id') : null,
        ];
    }

    private function fetchEmployeesForSummary(array $filters): Collection
    {
        $year = Carbon::parse($filters['month'] . '-01')->year;

        $employeesQuery = Employee::with([
            'sbu',
            'department',
            'assignedFloors' => static fn($q) => $q->where('is_active', true)->orderBy('floor_number'),
            'leaveQuotas' => function ($query) use ($year) {
                $query->where('year', $year)
                    ->with('leaveType');
            },
        ]);

        if (! empty($filters['sbu_id'])) {
            $this->viewerScope->assertSbuIdAllowed((int) $filters['sbu_id']);
            $employeesQuery->where('sbu_id', $filters['sbu_id']);
        }

        if (! empty($filters['department_id'])) {
            $this->viewerScope->assertDepartmentIdAccessible((int) $filters['department_id']);
            $employeesQuery->where('department_id', $filters['department_id']);
        }

        if (! empty($filters['floor_id'])) {
            $employeesQuery->whereHas(
                'assignedFloors',
                static fn($q) => $q->where('sbu_floors.id', (int) $filters['floor_id'])
            );
        }

        $this->viewerScope->applyViewerScopeToEmployeeQuery($employeesQuery);

        return $employeesQuery->get();
    }

    private function resolveSummaryFilterLabels(array $filters): array
    {
        $labels = [];

        if (! empty($filters['sbu_id'])) {
            $name = Sbu::query()->find($filters['sbu_id'])?->name;
            if ($name) {
                $labels[] = 'SBU: ' . $name;
            }
        }

        if (! empty($filters['department_id'])) {
            $name = Department::query()->find($filters['department_id'])?->name;
            if ($name) {
                $labels[] = 'Department: ' . $name;
            }
        }

        if (! empty($filters['floor_id'])) {
            $floor = SbuFloor::query()->find($filters['floor_id']);
            if ($floor) {
                $labels[] = 'Floor: ' . $floor->name;
            }
        }

        return $labels;
    }

    /**
     * @param  array<int>  $employeeSbuIds
     * @return Collection<int, LeaveType>
     */
    private function resolveTableLeaveTypes(?int $filteredSbuId, array $employeeSbuIds): Collection
    {
        $sbuIds = $filteredSbuId !== null
            ? [$filteredSbuId]
            : array_values(array_unique(array_filter($employeeSbuIds)));

        if ($sbuIds === []) {
            return collect();
        }

        $linkedLeaveTypeIds = DB::table('leave_type_sbu')
            ->whereIn('sbu_id', $sbuIds)
            ->pluck('leave_type_id')
            ->unique()
            ->values();

        return LeaveType::query()
            ->where('is_active', true)
            ->where(function ($query) use ($sbuIds, $linkedLeaveTypeIds) {
                $query->whereIn('sbu_id', $sbuIds);

                if ($linkedLeaveTypeIds->isNotEmpty()) {
                    $query->orWhereIn('id', $linkedLeaveTypeIds);
                }
            })
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }
    //old monthly summary builder based on employee leave quotas used annually and attendance stats
    // private function buildEmployeeMonthlySummary(Employee $employee, string $month, Collection $tableLeaveTypes): array
    // {
    //     $startDate = Carbon::parse($month . '-01')->startOfMonth();
    //     $quotaByLeaveType = $employee->leaveQuotas->keyBy('leave_type_id');

    //     $leaveUsage = $tableLeaveTypes->mapWithKeys(function (LeaveType $leaveType) use ($quotaByLeaveType) {
    //         return [
    //             (string) $leaveType->id => (float) ($quotaByLeaveType->get($leaveType->id)?->used ?? 0),
    //         ];
    //     })->all();

    //     $floors = $employee->relationLoaded('assignedFloors') ? $employee->assignedFloors : collect();
    //     $floorNames = $floors->pluck('name')->filter()->implode(', ');
    //     $sbuFloorIds = $floors->pluck('id')->map(static fn ($id) => (string) $id)->values()->all();
    //     $firstFloor = $floors->first();
    //     $attendance = $this->computeEmployeeMonthlyAttendance($employee, $month);
    //     $stats = $attendance['stats'];

    //     return [
    //         'employee_id' => $employee->id,
    //         'employee_code' => $employee->employee_code ?? 'N/A',
    //         'employee_name' => $employee->full_name ?? 'N/A',
    //         'employee_avatar' => strtoupper(substr($employee->full_name ?? 'E', 0, 1)),
    //         'department' => $employee->department->name ?? 'N/A',
    //         'department_id' => $employee->department_id,
    //         'sbu' => $employee->sbu->name ?? 'N/A',
    //         'sbu_id' => $employee->sbu_id,
    //         'floor_name' => $floorNames !== '' ? $floorNames : 'N/A',
    //         'sbu_floor_ids' => $sbuFloorIds,
    //         'floor_number' => $firstFloor?->floor_number,

    //         'total_days' => $startDate->daysInMonth,
    //         'present' => $stats['present'],
    //         'absent' => $stats['absent'],
    //         'half_days' => $stats['half_days'],
    //         'leave_usage' => $leaveUsage,

    //         'late_arrivals' => 0,
    //         'early_departures' => 0,
    //         'zone2_verification' => 'N/A',
    //         'regularization' => 0,
    //     ];
    // }

    //new monthly summary builder based on employee leave usage on selected month computed from attendance stats
    private function buildEmployeeMonthlySummary(Employee $employee, string $month, Collection $tableLeaveTypes): array
    {
        $startDate = Carbon::parse($month . '-01')->startOfMonth();

        $floors = $employee->relationLoaded('assignedFloors') ? $employee->assignedFloors : collect();
        $floorNames = $floors->pluck('name')->filter()->implode(', ');
        $sbuFloorIds = $floors->pluck('id')->map(static fn($id) => (string) $id)->values()->all();
        $firstFloor = $floors->first();
        $attendance = $this->computeEmployeeMonthlyAttendance($employee, $month);
        $stats = $attendance['stats'];
        $usageByType = $attendance['leave_usage'] ?? [];

        $leaveUsage = $tableLeaveTypes->mapWithKeys(function (LeaveType $leaveType) use ($usageByType) {
            $key = (string) $leaveType->id;

            return [
                $key => (float) ($usageByType[$key] ?? 0),
            ];
        })->all();

        return [
            'employee_id' => $employee->id,
            'employee_code' => $employee->employee_code ?? 'N/A',
            'employee_name' => $employee->full_name ?? 'N/A',
            'employee_avatar' => strtoupper(substr($employee->full_name ?? 'E', 0, 1)),
            'department' => $employee->department->name ?? 'N/A',
            'department_id' => $employee->department_id,
            'sbu' => $employee->sbu->name ?? 'N/A',
            'sbu_id' => $employee->sbu_id,
            'floor_name' => $floorNames !== '' ? $floorNames : 'N/A',
            'sbu_floor_ids' => $sbuFloorIds,
            'floor_number' => $firstFloor?->floor_number,

            'total_days' => $startDate->daysInMonth,
            'present' => $stats['present'],
            'absent' => $stats['absent'],
            'half_days' => $stats['half_days'],
            'leave_usage' => $leaveUsage,

            'late_arrivals' => 0,
            'early_departures' => 0,
            'zone2_verification' => 'N/A',
            'regularization' => 0,
        ];
    }
}
