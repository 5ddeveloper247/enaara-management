<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeLeaveEntity;
use App\Models\Organization;
use App\Models\Sbu;
use App\Models\ShiftRosterEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MonthlySummaryService
{
    public function __construct(
        private readonly PublicHolidayResolver $publicHolidayResolver,
    ) {}

    public function index(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $sbuId = $request->get('sbu_id');
        $departmentId = $request->get('department_id');
        $floorId = $request->get('floor_id');

        $year = Carbon::parse($month . '-01')->year;

        $sbus = \App\Models\Sbu::query()->orderBy('name')->get();
        $departments = \App\Models\Department::query()->orderBy('name')->get();

        $floorsForFilter = \App\Models\SbuFloor::query()
            ->where('is_active', true)
            ->orderBy('sbu_id')
            ->orderBy('floor_number')
            ->orderBy('name')
            ->get(['id', 'sbu_id', 'name', 'floor_number'])
            ->map(fn ($f) => [
                'id' => $f->id,
                'sbu_id' => $f->sbu_id,
                'name' => $f->name,
                'floor_number' => $f->floor_number,
            ])
            ->values()
            ->all();

        $employeesQuery = Employee::with([
            'sbu',
            'department',
            'assignedFloors' => static fn ($q) => $q->where('is_active', true)->orderBy('floor_number'),
            'leaveQuotas' => function ($query) use ($year) {
                $query->where('year', $year)
                    ->with('leaveType');
            },
        ]);

        if (! empty($sbuId)) {
            $employeesQuery->where('sbu_id', $sbuId);
        }

        if (! empty($departmentId)) {
            $employeesQuery->where('department_id', $departmentId);
        }

        if ($floorId !== null && $floorId !== '') {
            $fid = (int) $floorId;
            if ($fid > 0) {
                $employeesQuery->whereHas('assignedFloors', static fn ($q) => $q->where('sbu_floors.id', $fid));
            }
        }

        $employees = $employeesQuery->get();

        $monthlySummary = $employees->map(function ($employee) use ($month) {
            return $this->buildEmployeeMonthlySummary($employee, $month);
        })->values();

        return view('admin.monthly-summary.index', compact(
            'monthlySummary',
            'sbus',
            'departments',
            'month',
            'floorsForFilter'
        ));
    }

    public function getEmployeeMonthlyCalendar(int $employeeId, string $month): array
    {
        $employee = Employee::with(['department', 'sbu', 'organization'])->findOrFail($employeeId);
        $attendance = $this->computeEmployeeMonthlyAttendance($employee, $month);

        return [
            'employee_id' => $employee->id,
            'month' => $month,
            'engagement_mode' => $employee->engagement_mode,
            'days' => $attendance['days'],
            'stats' => $attendance['stats'],
        ];
    }

    private function computeEmployeeMonthlyAttendance(Employee $employee, string $month): array
    {
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $holidays = $this->publicHolidayResolver->loadHolidaysForRange($startDate, $endDate);

        $rosterEntries = ShiftRosterEntry::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('roster_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(static fn (ShiftRosterEntry $entry) => $entry->roster_date->toDateString());

        $leaveEntities = EmployeLeaveEntity::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('leave_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereIn('status', [0, 1])
            ->with('leaveType:id,name,code')
            ->get()
            ->keyBy(static fn (EmployeLeaveEntity $entity) => $entity->leave_date->toDateString());

        $workingDays = $this->resolveWorkingDays($employee);
        $isShiftBased = $employee->engagement_mode === 'shifts';

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

            $days[] = $day;
            $this->incrementCalendarStat($stats, $day);

            $cursor->addDay();
        }

        return [
            'days' => $days,
            'stats' => $stats,
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

        if ($holiday) {
            return [
                'date' => $dateStr,
                'day' => (int) $date->day,
                'status' => 'holiday',
                'label' => 'Holiday',
                'detail' => $holiday->name,
            ];
        }

        if ($leaveEntity) {
            $leaveTypeName = $leaveEntity->leaveType?->name ?? 'Leave';
            $duration = (float) $leaveEntity->duration;
            $isHalfDay = $duration > 0 && $duration < 1;
            $session = $leaveEntity->half_day_session
                ? strtoupper((string) $leaveEntity->half_day_session)
                : null;

            $label = $isHalfDay ? 'Half-day' : 'Leave';
            $detail = $isHalfDay
                ? trim($leaveTypeName . ($session ? " ({$session})" : ''))
                : $leaveTypeName;

            return [
                'date' => $dateStr,
                'day' => (int) $date->day,
                'status' => $isHalfDay ? 'half-day' : 'leave',
                'label' => $label,
                'detail' => $detail,
                'leave_type' => $leaveTypeName,
                'duration' => $duration,
            ];
        }

        if ($isShiftBased) {
            return $this->resolveShiftBasedDay($date, $rosterEntry);
        }

        return $this->resolveStandardDay($date, $workingDays);
    }

    private function resolveShiftBasedDay(Carbon $date, ?ShiftRosterEntry $rosterEntry): array
    {
        $base = [
            'date' => $date->toDateString(),
            'day' => (int) $date->day,
        ];

        if (! $rosterEntry) {
            return array_merge($base, [
                'status' => 'off',
                'label' => 'Off',
                'detail' => 'No shift scheduled',
            ]);
        }

        $status = strtolower(trim((string) $rosterEntry->status));

        if ($status === 'off') {
            return array_merge($base, [
                'status' => 'off',
                'label' => 'Off',
                'detail' => 'Scheduled off day',
            ]);
        }

        if (in_array($status, ['cancelled'], true)) {
            return array_merge($base, [
                'status' => 'off',
                'label' => 'Off',
                'detail' => 'Shift cancelled',
            ]);
        }

        return array_merge($base, [
            'status' => 'present',
            'label' => 'Present',
            'detail' => 'Working day',
        ]);
    }

    private function resolveStandardDay(Carbon $date, ?array $workingDays): array
    {
        $base = [
            'date' => $date->toDateString(),
            'day' => (int) $date->day,
        ];

        $dayKey = strtolower($date->format('l'));
        $isWorkingDay = $workingDays ? in_array($dayKey, $workingDays, true) : ! $date->isSunday();

        if (! $isWorkingDay) {
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

    private function resolveWorkingDays(Employee $employee): ?array
    {
        if (is_array($employee->working_days) && $employee->working_days !== []) {
            return array_values(array_map(static fn ($day) => strtolower((string) $day), $employee->working_days));
        }

        if ($employee->department_id) {
            $department = Department::query()->find($employee->department_id);
            if (is_array($department?->working_days) && $department->working_days !== []) {
                return array_values(array_map(static fn ($day) => strtolower((string) $day), $department->working_days));
            }
        }

        if ($employee->sbu_id) {
            $sbu = Sbu::query()->find($employee->sbu_id);
            if (is_array($sbu?->working_days) && $sbu->working_days !== []) {
                return array_values(array_map(static fn ($day) => strtolower((string) $day), $sbu->working_days));
            }
        }

        if ($employee->organization_id) {
            $organization = Organization::query()->find($employee->organization_id);
            if (is_array($organization?->working_days) && $organization->working_days !== []) {
                return array_values(array_map(static fn ($day) => strtolower((string) $day), $organization->working_days));
            }
        }

        return null;
    }

    private function incrementCalendarStat(array &$stats, array $day): void
    {
        match ($day['status']) {
            'present' => $stats['present']++,
            'half-day' => $stats['half_days']++,
            'leave' => $stats['leave']++,
            'off' => $stats['off']++,
            'holiday' => $stats['holiday']++,
            default => null,
        };
    }

    private function buildEmployeeMonthlySummary(Employee $employee, string $month): array
    {
        $startDate = Carbon::parse($month . '-01')->startOfMonth();

        $annualLeaveUsed = 0;
        $sickLeaveUsed = 0;
        $casualLeaveUsed = 0;

        foreach ($employee->leaveQuotas as $quota) {
            $leaveTypeName = strtolower(trim($quota->leaveType->name ?? ''));

            if (str_contains($leaveTypeName, 'annual')) {
                $annualLeaveUsed = (float) $quota->used;
            } elseif (str_contains($leaveTypeName, 'sick')) {
                $sickLeaveUsed = (float) $quota->used;
            } elseif (str_contains($leaveTypeName, 'casual')) {
                $casualLeaveUsed = (float) $quota->used;
            }
        }

        $floors = $employee->relationLoaded('assignedFloors') ? $employee->assignedFloors : collect();
        $floorNames = $floors->pluck('name')->filter()->implode(', ');
        $sbuFloorIds = $floors->pluck('id')->map(static fn ($id) => (string) $id)->values()->all();
        $firstFloor = $floors->first();
        $attendance = $this->computeEmployeeMonthlyAttendance($employee, $month);
        $stats = $attendance['stats'];

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

            'annual_leave' => $annualLeaveUsed,
            'sick_leave' => $sickLeaveUsed,
            'casual_leave' => $casualLeaveUsed,

            'late_arrivals' => 0,
            'early_departures' => 0,
            'zone2_verification' => 'N/A',
            'regularization' => 0,
        ];
    }
}
