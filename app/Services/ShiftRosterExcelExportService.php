<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use App\Models\OutsourcedEmployee;
use App\Models\ShiftRosterEntry;
use App\Models\ThirdParty;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ShiftRosterExcelExportService
{
    public function listDepartments(string $employeeGroup): array
    {
        if ($employeeGroup === 'third_party') {
            $companyIds = OutsourcedEmployee::query()
                ->whereNull('deleted_at')
                ->whereNotNull('contractor_company_id')
                ->distinct()
                ->pluck('contractor_company_id');

            return ThirdParty::query()
                ->whereIn('id', $companyIds)
                ->orderBy('third_party_name')
                ->get(['id', 'third_party_name'])
                ->map(fn (ThirdParty $company) => [
                    'id' => $company->id,
                    'name' => (string) $company->third_party_name,
                ])
                ->values()
                ->all();
        }

        $departmentIds = Employee::query()
            ->where('is_active', 1)
            ->shiftBasedWorkArrangement()
            ->whereNotNull('department_id')
            ->distinct()
            ->pluck('department_id');

        return Department::query()
            ->whereIn('id', $departmentIds)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Department $department) => [
                'id' => $department->id,
                'name' => (string) $department->name,
            ])
            ->values()
            ->all();
    }

    public function buildPayload(array $options): array
    {
        $context = $this->resolveContext($options);
        $days = $this->buildCalendarDays($context['date_range']);
        $columns = $this->buildCalendarColumns($days);
        $keys = array_column($columns, 'key');

        $entries = $this->fetchEntries($context);
        $entriesByAssigneeDate = $this->groupEntriesByAssigneeAndDate($entries);
        $assignees = $this->fetchAssignees($context);

        $rows = $assignees
            ->sortBy([
                ['department_sort', 'asc'],
                ['employee_name', 'asc'],
            ])
            ->map(function (array $assignee) use ($days, $entriesByAssigneeDate, $keys) {
                $row = [
                    'employee_name' => $assignee['employee_name'],
                    'employee_code' => $assignee['employee_code'],
                    'department' => $assignee['department'],
                ];

                foreach ($days as $day) {
                    $dateKey = $day['date_key'];
                    $columnKey = $this->dateColumnKey($dateKey);
                    $entry = $entriesByAssigneeDate[$assignee['assignee_key']][$dateKey] ?? null;
                    $row[$columnKey] = $entry ? $this->formatCalendarCell($entry) : '';
                }

                $payload = [];
                foreach ($keys as $key) {
                    $payload[$key] = $row[$key] ?? '';
                }

                return $payload;
            })
            ->values()
            ->all();

        $employeeGroup = $options['employee_group'] ?? 'internal';

        return [
            'layout' => 'calendar',
            'columns' => $columns,
            'rows' => $rows,
            'sheet_name' => 'Shift Roster',
            'file_prefix' => 'shift-roster-' . strtolower($context['period_slug']) . '-' . $employeeGroup,
            'period_label' => $context['period_label'],
        ];
    }

    private function buildCalendarColumns(array $days): array
    {
        $columns = [
            ['header' => 'Employee Name', 'key' => 'employee_name'],
            ['header' => 'Employee Code', 'key' => 'employee_code'],
            ['header' => 'Department', 'key' => 'department'],
        ];

        foreach ($days as $day) {
            $columns[] = [
                'header' => $day['day'] . ' ' . $day['dow'],
                'key' => $this->dateColumnKey($day['date_key']),
            ];
        }

        return $columns;
    }

    private function buildCalendarDays(array $dateRange): array
    {
        $start = Carbon::parse($dateRange[0])->startOfDay();
        $end = Carbon::parse($dateRange[1])->startOfDay();
        $days = [];

        for ($date = $start->copy(); $date->lessThanOrEqualTo($end); $date->addDay()) {
            $days[] = [
                'day' => $date->format('d'),
                'dow' => $date->format('D'),
                'date_key' => $date->format('Y-m-d'),
            ];
        }

        return $days;
    }

    private function dateColumnKey(string $dateKey): string
    {
        return 'date_' . str_replace('-', '_', $dateKey);
    }

    private function resolveContext(array $options): array
    {
        $year = (int) $options['year'];
        $month = (int) $options['month'];
        $period = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $periodEnd = $period->copy()->endOfMonth();

        return [
            'employee_group' => $options['employee_group'] ?? 'internal',
            'department_id' => ! empty($options['department_id']) ? (int) $options['department_id'] : null,
            'date_range' => [$period->toDateString(), $periodEnd->toDateString()],
            'period_label' => $period->format('F Y'),
            'period_slug' => $period->format('F-Y'),
        ];
    }

    private function fetchAssignees(array $context): Collection
    {
        $departmentId = $context['department_id'] ?? null;

        if ($context['employee_group'] === 'third_party') {
            $query = OutsourcedEmployee::with('contractorCompany')
                ->whereNull('deleted_at');

            if ($departmentId) {
                $query->where('contractor_company_id', $departmentId);
            }

            return $query->get()
                ->map(function (OutsourcedEmployee $employee) {
                    $department = $employee->contractorCompany?->third_party_name ?? 'Unassigned';

                    return [
                        'assignee_key' => 'outsourced:' . $employee->id,
                        'employee_name' => trim((string) ($employee->full_name ?? '')) ?: 'Unknown',
                        'employee_code' => $employee->biometric_id
                            ? (string) $employee->biometric_id
                            : ('OSP-' . $employee->id),
                        'department' => $department,
                        'department_sort' => mb_strtolower($department),
                    ];
                });
        }

        $query = Employee::with('department')
            ->where('is_active', 1)
            ->shiftBasedWorkArrangement();

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        return $query->get()
            ->map(function (Employee $employee) {
                $department = $employee->department?->name ?? 'Unassigned';

                return [
                    'assignee_key' => 'employee:' . $employee->id,
                    'employee_name' => $this->resolveEmployeeFullName($employee),
                    'employee_code' => trim((string) ($employee->employee_code ?? '')),
                    'department' => $department,
                    'department_sort' => mb_strtolower($department),
                ];
            });
    }

    private function resolveEmployeeFullName(Employee $employee): string
    {
        return $employee->rosterDisplayName();
    }

    private function groupEntriesByAssigneeAndDate(Collection $entries): array
    {
        $grouped = [];

        foreach ($entries as $entry) {
            $assigneeKey = $this->assigneeKey($entry);
            $dateKey = $entry->roster_date->format('Y-m-d');

            if (! isset($grouped[$assigneeKey])) {
                $grouped[$assigneeKey] = [];
            }

            $grouped[$assigneeKey][$dateKey] = $entry;
        }

        return $grouped;
    }

    private function assigneeKey(ShiftRosterEntry $entry): string
    {
        if ($entry->employee_id) {
            return 'employee:' . $entry->employee_id;
        }

        return 'outsourced:' . $entry->outsourced_employee_id;
    }

    private function fetchEntries(array $context): Collection
    {
        $relations = [
            'shift',
            'employee.department',
            'outsourcedEmployee.contractorCompany',
        ];

        $query = ShiftRosterEntry::with($relations)
            ->whereBetween('roster_date', $context['date_range'])
            ->orderBy('roster_date')
            ->orderBy('id');

        if ($context['employee_group'] === 'third_party') {
            $query->whereNotNull('outsourced_employee_id');

            if (! empty($context['department_id'])) {
                $query->whereHas('outsourcedEmployee', function ($employeeQuery) use ($context) {
                    $employeeQuery->where('contractor_company_id', $context['department_id']);
                });
            }
        } else {
            $query->whereNotNull('employee_id');

            if (! empty($context['department_id'])) {
                $query->whereHas('employee', function ($employeeQuery) use ($context) {
                    $employeeQuery->where('department_id', $context['department_id']);
                });
            }
        }

        return $query->get();
    }

    private function formatCalendarCell(ShiftRosterEntry $entry): string
    {
        $status = strtolower((string) $entry->status);

        if ($status === 'off') {
            return 'OFF';
        }

        if ($status === 'cancelled') {
            return 'Cancelled';
        }

        $isOffDay = false;
        $shiftNameRaw = strtolower($entry->shift?->name ?? '');
        $shiftCodeRaw = strtolower($entry->shift?->code ?? '');
        $isCustomTime = (bool) $entry->is_custom_time;
        $startRaw = $entry->start_time ?? $entry->shift?->start_time;
        $endRaw = $entry->end_time ?? $entry->shift?->end_time;
        $shiftType = $this->resolveShiftType(
            $startRaw ? $this->formatShiftTime($startRaw) : null,
            $isOffDay,
            $isCustomTime,
            $shiftNameRaw,
            $shiftCodeRaw
        );

        $displayShiftName = '';
        if ($entry->shift?->name) {
            $displayShiftName = (string) $entry->shift->name;
        } elseif ($isCustomTime) {
            $displayShiftName = 'Custom';
        } elseif ($shiftType !== 'general') {
            $displayShiftName = ucfirst($shiftType);
        } else {
            $displayShiftName = 'Shift';
        }

        $start = $this->formatDisplayTime($startRaw);
        $end = $this->formatDisplayTime($endRaw);

        if ($start && $end) {
            return $displayShiftName . "\n" . $start . ' - ' . $end;
        }

        return $displayShiftName;
    }

    private function formatShiftTime($value): string
    {
        if (! $value) {
            return '00:00';
        }

        return Carbon::parse($value)->format('H:i');
    }

    private function formatDisplayTime($value): ?string
    {
        if (! $value) {
            return null;
        }

        return Carbon::parse($value)->format('h:i A');
    }

    private function resolveShiftType(
        ?string $startTime,
        bool $isOffDay,
        bool $isCustomTime,
        string $shiftName = '',
        string $shiftCode = ''
    ): string {
        if ($isOffDay) {
            return 'off';
        }

        $shiftCode = strtolower($shiftCode);
        $shiftName = strtolower($shiftName);

        if ($shiftCode === 'gen' || ($shiftName !== '' && str_contains($shiftName, 'general'))) {
            return 'general';
        }

        if ($shiftName !== '') {
            if (str_contains($shiftName, 'morning')) {
                return 'morning';
            }
            if (str_contains($shiftName, 'evening')) {
                return 'evening';
            }
            if (str_contains($shiftName, 'night')) {
                return 'night';
            }
        }

        if ($startTime) {
            $hour = (int) Carbon::parse($startTime)->format('H');
            if ($hour >= 4 && $hour < 12) {
                return 'morning';
            }
            if ($hour >= 12 && $hour < 18) {
                return 'evening';
            }
            if ($hour >= 18 || $hour < 4) {
                return 'night';
            }
        }

        return 'general';
    }
}
