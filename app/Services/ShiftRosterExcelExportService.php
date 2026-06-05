<?php

namespace App\Services;

use App\Models\ShiftRosterEntry;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ShiftRosterExcelExportService
{
    public function buildPayload(array $options): array
    {
        $context = $this->resolveContext($options);
        $columns = $this->columns();
        $keys = array_column($columns, 'key');

        $rows = $this->collectRows($context)
            ->map(function (array $row) use ($keys) {
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
            'columns' => $columns,
            'rows' => $rows,
            'sheet_name' => 'Shift Roster',
            'file_prefix' => 'shift-roster-' . strtolower($context['period_slug']) . '-' . $employeeGroup,
            'period_label' => $context['period_label'],
        ];
    }

    private function columns(): array
    {
        return [
            ['header' => 'Employee Name', 'key' => 'employee_name'],
            ['header' => 'Employee Code', 'key' => 'employee_code'],
            ['header' => 'Employee Type', 'key' => 'employee_type'],
            ['header' => 'Department', 'key' => 'department'],
            ['header' => 'Roster Date', 'key' => 'roster_date'],
            ['header' => 'Day', 'key' => 'day'],
            ['header' => 'Status', 'key' => 'status'],
            ['header' => 'Shift Name', 'key' => 'shift_name'],
            ['header' => 'Shift Type', 'key' => 'shift_type'],
            ['header' => 'Start Time', 'key' => 'start_time'],
            ['header' => 'End Time', 'key' => 'end_time'],
            ['header' => 'Hours', 'key' => 'hours'],
            ['header' => 'Floor', 'key' => 'floor'],
            ['header' => 'Location', 'key' => 'location'],
            ['header' => 'Notes', 'key' => 'notes'],
            ['header' => 'Custom Time', 'key' => 'custom_time'],
            ['header' => 'Compensatory', 'key' => 'compensatory'],
            ['header' => 'Deleted', 'key' => 'deleted'],
        ];
    }

    private function resolveContext(array $options): array
    {
        $year = (int) $options['year'];
        $month = (int) $options['month'];
        $period = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $periodEnd = $period->copy()->endOfMonth();

        return [
            'employee_group' => $options['employee_group'] ?? 'internal',
            'include_deleted' => (bool) ($options['include_deleted'] ?? false),
            'date_range' => [$period->toDateString(), $periodEnd->toDateString()],
            'period_label' => $period->format('F Y'),
            'period_slug' => $period->format('F-Y'),
        ];
    }

    private function collectRows(array $context): Collection
    {
        return $this->fetchEntries($context)
            ->map(fn (ShiftRosterEntry $entry) => $this->mapEntry($entry))
            ->sortBy([
                ['roster_date_sort', 'asc'],
                ['department_sort', 'asc'],
                ['employee_name', 'asc'],
                ['entry_id', 'asc'],
            ])
            ->values();
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
        } else {
            $query->whereNotNull('employee_id');
        }

        $entries = $query->get();

        if ($context['include_deleted']) {
            $trashedQuery = ShiftRosterEntry::onlyTrashed()
                ->with($relations)
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

    private function mapEntry(ShiftRosterEntry $entry): array
    {
        $isOffDay = strtolower((string) $entry->status) === 'off';
        $shiftNameRaw = strtolower($entry->shift?->name ?? '');
        $isCustomTime = (bool) $entry->is_custom_time;
        $startRaw = $isOffDay ? null : ($entry->start_time ?? $entry->shift?->start_time);
        $endRaw = $isOffDay ? null : ($entry->end_time ?? $entry->shift?->end_time);
        $shiftType = $this->resolveShiftType(
            $startRaw ? $this->formatShiftTime($startRaw) : null,
            $isOffDay,
            $isCustomTime,
            $shiftNameRaw
        );

        $departmentName = $this->resolveDepartmentName($entry);
        $rosterDate = $entry->roster_date->copy();
        $status = strtolower((string) $entry->status);

        $displayShiftName = '';
        if ($isOffDay) {
            $displayShiftName = 'Off';
        } elseif ($entry->shift?->name) {
            $displayShiftName = (string) $entry->shift->name;
        } elseif ($isCustomTime) {
            $displayShiftName = 'Custom';
        }

        $shiftTypeLabel = match ($shiftType) {
            'off' => 'Off',
            'morning', 'evening', 'night' => ucfirst($shiftType),
            default => $entry->shift?->name ? 'General' : ($isCustomTime ? 'Custom' : ''),
        };

        return [
            'entry_id' => $entry->id,
            'employee_name' => $this->resolveEmployeeName($entry),
            'employee_code' => $this->resolveEmployeeCode($entry),
            'employee_type' => $entry->employee_id ? 'Internal' : 'Third-party',
            'department' => $departmentName,
            'department_sort' => mb_strtolower($departmentName),
            'roster_date' => $rosterDate->format('d M Y'),
            'roster_date_sort' => $rosterDate->format('Y-m-d'),
            'day' => $rosterDate->format('D'),
            'status' => ucfirst($status),
            'shift_name' => $displayShiftName,
            'shift_type' => $shiftTypeLabel,
            'start_time' => $this->formatDisplayTime($startRaw) ?? '',
            'end_time' => $this->formatDisplayTime($endRaw) ?? '',
            'hours' => $isOffDay ? 0 : $this->calculateEntryHours($startRaw, $endRaw),
            'floor' => trim((string) ($entry->floor ?? '')),
            'location' => trim((string) ($entry->location_text ?? '')),
            'notes' => trim((string) ($entry->notes ?? '')),
            'custom_time' => $isCustomTime ? 'Yes' : 'No',
            'compensatory' => $entry->is_compensatory_earned ? 'Yes' : 'No',
            'deleted' => $entry->trashed() ? 'Yes' : 'No',
        ];
    }

    private function resolveEmployeeName(ShiftRosterEntry $entry): string
    {
        if ($entry->employee_id && $entry->employee) {
            $employee = $entry->employee;
            $fullName = trim((string) ($employee->full_name ?? ''));
            if ($fullName !== '') {
                return $fullName;
            }

            $parts = array_filter([
                trim((string) ($employee->first_name ?? '')),
                trim((string) ($employee->middle_name ?? '')),
                trim((string) ($employee->last_name ?? '')),
            ], fn (string $part) => $part !== '');

            $composed = trim(implode(' ', $parts));

            return $composed !== '' ? $composed : 'Unknown';
        }

        if ($entry->outsourced_employee_id && $entry->outsourcedEmployee) {
            return trim((string) ($entry->outsourcedEmployee->full_name ?? '')) ?: 'Unknown';
        }

        return 'Unknown';
    }

    private function resolveEmployeeCode(ShiftRosterEntry $entry): string
    {
        if ($entry->employee_id && $entry->employee) {
            return trim((string) ($entry->employee->employee_code ?? ''));
        }

        if ($entry->outsourced_employee_id && $entry->outsourcedEmployee) {
            $biometricId = $entry->outsourcedEmployee->biometric_id;

            return $biometricId ? (string) $biometricId : ('OSP-' . $entry->outsourced_employee_id);
        }

        return '';
    }

    private function resolveDepartmentName(ShiftRosterEntry $entry): string
    {
        if ($entry->employee_id) {
            return $entry->employee?->department?->name ?? 'Unassigned';
        }

        return $entry->outsourcedEmployee?->contractorCompany?->third_party_name ?? 'Unassigned';
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
