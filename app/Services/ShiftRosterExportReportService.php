<?php

namespace App\Services;

use App\Models\ShiftRosterEntry;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ShiftRosterExportReportService
{
    public function buildTabularReport(array $options): array
    {
        $context = $this->resolveExportContext($options);
        $rows = $this->collectExportRows($context);

        $grouped = $rows->groupBy('department_key');
        $departments = $grouped
            ->map(function ($groupRows) {
                $first = $groupRows->first();

                return [
                    'name' => $first['department_name'],
                    'rows' => $groupRows
                        ->sortBy(['date_sort', 'employee'])
                        ->values()
                        ->map(fn (array $row) => $this->stripDepartmentMeta($row))
                        ->all(),
                ];
            })
            ->sortBy(fn (array $department) => $department['name'], SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();

        if (! $context['include_department_grouping']) {
            $departments = [
                [
                    'name' => null,
                    'rows' => $rows
                        ->sortBy(['date_sort', 'employee'])
                        ->values()
                        ->map(fn (array $row) => $this->stripDepartmentMeta($row))
                        ->all(),
                ],
            ];
        }

        $exportRows = $rows->map(fn (array $row) => $this->stripDepartmentMeta($row));

        return array_merge($this->buildReportShell($context), [
            'stats' => $this->buildStatsFromRows($exportRows),
            'departments' => $departments,
        ]);
    }

    public function buildCalendarReport(array $options): array
    {
        $context = $this->resolveExportContext($options);
        $days = $this->buildCalendarDays($context['date_range']);
        $dateKeys = array_column($days, 'date_key');

        $entries = $this->fetchExportEntries($context)
            ->filter(fn (ShiftRosterEntry $entry) => $this->isExportableRosterEntry($entry));

        $exportRows = $entries
            ->map(fn (ShiftRosterEntry $entry) => $this->mapEntryToExportRow($entry, $context['include_shift_times']))
            ->values();

        $employeeCount = $entries
            ->groupBy(fn (ShiftRosterEntry $entry) => $this->assigneeExportKey($entry))
            ->count();

        if (! $context['include_department_grouping']) {
            $departments = [
                [
                    'name' => null,
                    'employees' => $this->buildCalendarEmployees($entries, $dateKeys, $context),
                ],
            ];
        } else {
            $departments = $entries
                ->groupBy(fn (ShiftRosterEntry $entry) => $this->resolveExportDepartmentName($entry))
                ->map(function (Collection $departmentEntries, string $departmentName) use ($dateKeys, $context) {
                    return [
                        'name' => strtoupper($departmentName),
                        'employees' => $this->buildCalendarEmployees($departmentEntries, $dateKeys, $context),
                    ];
                })
                ->sortBy(fn (array $department) => $department['name'], SORT_NATURAL | SORT_FLAG_CASE)
                ->values()
                ->all();
        }

        return array_merge($this->buildReportShell($context), [
            'report_title' => 'Shift Planner — Shift Roster Export',
            'days' => $days,
            'departments' => $departments,
            'stats' => $this->buildStatsFromRows($exportRows, $employeeCount),
        ]);
    }

    public function buildPerEmployeeReport(array $options): array
    {
        $context = $this->resolveExportContext($options);
        $entries = $this->fetchExportEntries($context);

        $employees = $entries
            ->filter(fn (ShiftRosterEntry $entry) => $this->isExportableRosterEntry($entry))
            ->groupBy(fn (ShiftRosterEntry $entry) => $this->assigneeExportKey($entry))
            ->map(function (Collection $group) use ($context) {
                $first = $group->first();
                $name = $first->employee?->full_name
                    ?? $first->outsourcedEmployee?->full_name
                    ?? 'Unknown';

                $shifts = $group
                    ->sortBy(fn (ShiftRosterEntry $entry) => $entry->roster_date->format('Y-m-d'))
                    ->values()
                    ->map(fn (ShiftRosterEntry $entry) => $this->mapEntryToShiftRow($entry, $context['include_shift_times']))
                    ->all();

                $totalHours = (int) array_sum(array_column($shifts, 'hours'));

                return [
                    'initials' => $this->makeInitials($name),
                    'name' => $name,
                    'department' => $this->resolveExportDepartmentName($first),
                    'shift_count' => count($shifts),
                    'total_hours' => $totalHours,
                    'shifts' => $shifts,
                ];
            })
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();

        $flatRows = collect($employees)->flatMap(fn (array $employee) => $employee['shifts']);

        return array_merge($this->buildReportShell($context), [
            'stats' => $this->buildStatsFromRows($flatRows, count($employees)),
            'employees' => $employees,
        ]);
    }

    private function resolveExportContext(array $options): array
    {
        $employeeGroup = $options['employee_group'] ?? 'internal';
        $includeDeleted = (bool) ($options['include_deleted'] ?? false);
        $includeDepartmentGrouping = (bool) ($options['include_department_grouping'] ?? true);
        $includeShiftTimes = (bool) ($options['include_shift_times'] ?? true);

        if (($options['export_period_type'] ?? 'month') === 'date_range') {
            $period = Carbon::parse($options['start_date'])->startOfDay();
            $periodEnd = Carbon::parse($options['end_date'])->endOfDay();
            $dateRange = [$period->toDateString(), $periodEnd->toDateString()];
            $periodLabel = $period->format('d M Y') . ' – ' . $periodEnd->format('d M Y');
            $periodSlug = $period->format('Y-m-d') . '_to_' . $periodEnd->format('Y-m-d');
        } else {
            $year = (int) $options['year'];
            $month = (int) $options['month'];
            $period = Carbon::createFromDate($year, $month, 1)->startOfDay();
            $periodEnd = $period->copy()->endOfMonth();
            $dateRange = [$period->toDateString(), $periodEnd->toDateString()];
            $periodLabel = $period->format('F Y');
            $periodSlug = $period->format('F-Y');
        }

        return [
            'employee_group' => $employeeGroup,
            'include_deleted' => $includeDeleted,
            'include_department_grouping' => $includeDepartmentGrouping,
            'include_shift_times' => $includeShiftTimes,
            'date_range' => $dateRange,
            'period_label' => $periodLabel,
            'period_slug' => $periodSlug,
        ];
    }

    private function fetchExportEntries(array $context): Collection
    {
        $entryRelations = [
            'shift',
            'employee.department',
            'outsourcedEmployee.contractorCompany',
        ];

        $entriesQuery = ShiftRosterEntry::with($entryRelations)
            ->whereBetween('roster_date', $context['date_range'])
            ->orderBy('roster_date')
            ->orderBy('id');

        if ($context['employee_group'] === 'third_party') {
            $entriesQuery->whereNotNull('outsourced_employee_id');
        } else {
            $entriesQuery->whereNotNull('employee_id');
        }

        $entries = $entriesQuery->get();

        if ($context['include_deleted']) {
            $trashedQuery = ShiftRosterEntry::onlyTrashed()
                ->with($entryRelations)
                ->whereBetween('roster_date', $context['date_range'])
                ->orderBy('roster_date')
                ->orderBy('id');

            if ($context['employee_group'] === 'third_party') {
                $trashedQuery->whereNotNull('outsourced_employee_id');
            } else {
                $trashedQuery->whereNotNull('employee_id');
            }

            $entries = $entries->merge($trashedQuery->get())->unique('id')->values();
        }

        return $entries;
    }

    private function collectExportRows(array $context): Collection
    {
        return $this->fetchExportEntries($context)
            ->filter(fn (ShiftRosterEntry $entry) => $this->isExportableRosterEntry($entry))
            ->map(fn (ShiftRosterEntry $entry) => $this->mapEntryToExportRow($entry, $context['include_shift_times']))
            ->values();
    }

    private function buildReportShell(array $context): array
    {
        return [
            'organization_name' => config('app.name', 'Enaara Systems'),
            'report_title' => 'Shift Planner — Monthly Report',
            'period_label' => $context['period_label'],
            'period_slug' => $context['period_slug'],
            'generated_at' => now()->format('d M Y, h:i A'),
            'employee_group_label' => $context['employee_group'] === 'third_party'
                ? 'Third-party employees'
                : 'Internal employees',
            'include_shift_times' => $context['include_shift_times'],
            'include_department_grouping' => $context['include_department_grouping'],
            'include_deleted' => $context['include_deleted'],
        ];
    }

    private function buildStatsFromRows(Collection $rows, ?int $employeeCount = null): array
    {
        $employeeNames = $rows->pluck('employee')->filter()->unique();

        return [
            'total_employees' => $employeeCount ?? $employeeNames->count(),
            'shifts_scheduled' => $rows->count(),
            'morning' => $rows->where('shift_type', 'morning')->count(),
            'evening' => $rows->where('shift_type', 'evening')->count(),
            'night' => $rows->where('shift_type', 'night')->count(),
            'total_hours' => (int) $rows->sum('hours'),
        ];
    }

    private function assigneeExportKey(ShiftRosterEntry $entry): string
    {
        if ($entry->employee_id) {
            return 'employee:' . $entry->employee_id;
        }

        return 'outsourced:' . $entry->outsourced_employee_id;
    }

    private function makeInitials(string $fullName): string
    {
        $parts = preg_split('/\s+/u', trim($fullName), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($parts) >= 2) {
            return strtoupper(
                mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts) - 1], 0, 1)
            );
        }

        return strtoupper(mb_substr(trim($fullName), 0, 2));
    }

    private function isExportableRosterEntry(ShiftRosterEntry $entry): bool
    {
        $status = strtolower((string) $entry->status);

        return ! in_array($status, ['off', 'cancelled'], true);
    }

    private function mapEntryToExportRow(ShiftRosterEntry $entry, bool $includeShiftTimes): array
    {
        $isOffDay = strtolower((string) $entry->status) === 'off';
        $shiftName = strtolower($entry->shift?->name ?? '');
        $isCustomTime = (bool) $entry->is_custom_time;
        $startRaw = $isOffDay ? null : ($entry->start_time ?? $entry->shift?->start_time);
        $endRaw = $isOffDay ? null : ($entry->end_time ?? $entry->shift?->end_time);
        $shiftType = $this->resolveShiftType(
            $startRaw ? $this->formatShiftTime($startRaw) : null,
            $isOffDay,
            $isCustomTime,
            $shiftName
        );

        $employeeName = $entry->employee?->full_name
            ?? $entry->outsourcedEmployee?->full_name
            ?? 'Unknown';

        $departmentName = $this->resolveExportDepartmentName($entry);
        $shiftLabel = $shiftType === 'general'
            ? ($entry->shift?->name ?? 'Custom')
            : ucfirst($shiftType);

        $rosterDate = $entry->roster_date->copy();

        return [
            'employee' => $employeeName,
            'date' => $rosterDate->format('d M'),
            'date_sort' => $rosterDate->format('Y-m-d'),
            'day' => $rosterDate->format('D'),
            'start_time' => $includeShiftTimes ? $this->formatDisplayTime($startRaw) : null,
            'end_time' => $includeShiftTimes ? $this->formatDisplayTime($endRaw) : null,
            'shift_type' => $shiftType,
            'shift_label' => $shiftLabel,
            'hours' => $this->calculateEntryHours($startRaw, $endRaw),
            'is_deleted' => $entry->trashed(),
            'department_key' => $departmentName,
            'department_name' => strtoupper($departmentName),
        ];
    }

    private function mapEntryToShiftRow(ShiftRosterEntry $entry, bool $includeShiftTimes): array
    {
        $row = $this->mapEntryToExportRow($entry, $includeShiftTimes);
        unset($row['employee'], $row['department_key'], $row['department_name']);

        return $row;
    }

    private function buildCalendarDays(array $dateRange): array
    {
        $start = Carbon::parse($dateRange[0])->startOfDay();
        $end = Carbon::parse($dateRange[1])->startOfDay();
        $days = [];

        for ($date = $start->copy(); $date->lessThanOrEqualTo($end); $date->addDay()) {
            $days[] = [
                'day' => $date->format('d'),
                'dow' => strtoupper($date->format('D')),
                'date_key' => $date->format('Y-m-d'),
            ];
        }

        return $days;
    }

    private function buildCalendarEmployees(Collection $entries, array $dateKeys, array $context): array
    {
        return $entries
            ->groupBy(fn (ShiftRosterEntry $entry) => $this->assigneeExportKey($entry))
            ->map(function (Collection $group) use ($dateKeys, $context) {
                $first = $group->first();
                $name = $first->employee?->full_name
                    ?? $first->outsourcedEmployee?->full_name
                    ?? 'Unknown';

                $cellsByDate = [];

                foreach ($group->sortBy(fn (ShiftRosterEntry $entry) => $entry->roster_date->format('Y-m-d') . '-' . $entry->id) as $entry) {
                    $cellsByDate[$entry->roster_date->format('Y-m-d')] = $this->mapEntryToCalendarCell(
                        $entry,
                        $context['include_shift_times']
                    );
                }

                $cells = [];

                foreach ($dateKeys as $dateKey) {
                    $cells[] = $cellsByDate[$dateKey] ?? null;
                }

                return [
                    'name' => $name,
                    'cells' => $cells,
                ];
            })
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    private function mapEntryToCalendarCell(ShiftRosterEntry $entry, bool $includeShiftTimes): array
    {
        $row = $this->mapEntryToExportRow($entry, $includeShiftTimes);

        return [
            'shift_type' => $row['shift_type'],
            'shift_label' => $row['shift_label'],
            'time_start' => $row['start_time'],
            'time_end' => $row['end_time'],
            'hours' => $row['hours'],
            'is_deleted' => $row['is_deleted'],
        ];
    }

    private function resolveExportDepartmentName(ShiftRosterEntry $entry): string
    {
        if ($entry->employee_id) {
            return $entry->employee?->department?->name ?? 'Unassigned';
        }

        return $entry->outsourcedEmployee?->contractorCompany?->third_party_name ?? 'Unassigned';
    }

    private function stripDepartmentMeta(array $row): array
    {
        unset($row['department_key'], $row['department_name']);

        return $row;
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

    private function calculateEntryHours($startTime, $endTime): int
    {
        if (! $startTime || ! $endTime) {
            return 0;
        }

        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        $minutes = $start->diffInMinutes($end);

        return max(1, (int) round($minutes / 60));
    }

    private function resolveShiftType(?string $startTime, bool $isOffDay, bool $isCustomTime, string $shiftName = ''): string
    {
        if ($isOffDay) {
            return 'off';
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

        if (! $isCustomTime && $shiftName !== '') {
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

        return 'general';
    }
}
