<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\OutsourcedEmployee;
use App\Models\ShiftPlanner;
use App\Models\ShiftRosterEntry;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShiftRosterService
{
    /**
     * Store a newly created shift roster (Single Day).
     */
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $shift = ShiftPlanner::findOrFail((int) $data['shift_planner_id']);

            $payload = [
                'shift_planner_id' => $shift->id,
                'roster_date' => $data['roster_date'],
                'start_time' => $data['start_time'] ?? $this->formatShiftTime($shift->start_time),
                'end_time' => $data['end_time'] ?? $this->formatShiftTime($shift->end_time),
                'check_in' => $data['check_in'] ?? null,
                'check_out' => $data['check_out'] ?? null,
                'floor' => $data['floor'] ?? null,
                'late_check_in' => (bool) ($data['late_check_in'] ?? false),
                'status' => (($data['status'] ?? 1) == 1) ? 'pending' : 'cancelled',
                'assignment_id' => null,
            ];

            if (($data['employee_type'] ?? 'employee') === 'outsourced') {
                $payload['employee_id'] = null;
                $payload['outsourced_employee_id'] = (int) $data['employee_id'];
                return ShiftRosterEntry::updateOrCreate(
                    [
                        'outsourced_employee_id' => (int) $data['employee_id'],
                        'roster_date' => $data['roster_date'],
                    ],
                    $payload
                );
            }

            $payload['employee_id'] = (int) $data['employee_id'];
            $payload['outsourced_employee_id'] = null;
            return ShiftRosterEntry::updateOrCreate(
                [
                    'employee_id' => (int) $data['employee_id'],
                    'roster_date' => $data['roster_date'],
                ],
                $payload
            );
        });
    }

    /**
     * Update an existing shift roster entry.
     */
    public function update(array $data, $id)
    {
        $entry = ShiftRosterEntry::findOrFail($id);

        $payload = [
            'shift_planner_id' => $data['shift_planner_id'],
            'roster_date' => $data['roster_date'],
            'start_time' => $data['start_time'] ?? $entry->start_time,
            'end_time' => $data['end_time'] ?? $entry->end_time,
            'check_in' => $data['check_in'] ?? $entry->check_in,
            'check_out' => $data['check_out'] ?? $entry->check_out,
            'floor' => $data['floor'] ?? $entry->floor,
            'late_check_in' => (bool) ($data['late_check_in'] ?? $entry->late_check_in),
            'status' => isset($data['status']) ? ((int) $data['status'] === 1 ? 'pending' : 'cancelled') : $entry->status,
        ];

        if (($data['employee_type'] ?? 'employee') === 'outsourced') {
            $payload['outsourced_employee_id'] = (int) $data['employee_id'];
            $payload['employee_id'] = null;
        } else {
            $payload['employee_id'] = (int) $data['employee_id'];
            $payload['outsourced_employee_id'] = null;
        }

        $entry->update($payload);

        return $entry;
    }

    /**
     * Delete a shift roster entry.
     */
    public function destroy($id)
    {
        $entry = ShiftRosterEntry::findOrFail($id);
        return $entry->delete();
    }

    /**
     * Bulk assign shifts to multiple employees across a date range.
     */
    public function bulkAssign(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $shift = ShiftPlanner::findOrFail((int) $data['shift_planner_id']);
            $refs = $this->parseAssigneeRefs($data['employee_ids'] ?? []);
            $days = array_map('strtolower', $data['days'] ?? []);
            $excludeWeekends = (bool) ($data['exclude_weekends'] ?? false);
            $checkConflicts = (bool) ($data['check_conflicts'] ?? true);
            $overrideExisting = (bool) ($data['override_existing'] ?? false);

            if ($checkConflicts && ! $overrideExisting) {
                $conflicts = $this->collectBulkConflicts(
                    $refs,
                    $data['start_date'],
                    $data['end_date'],
                    $days
                );
                if ($conflicts !== []) {
                    return [
                        'success' => false,
                        'message' => 'Some employees already have shifts assigned on selected days: ' . implode(', ', $conflicts),
                        'conflicts' => $conflicts,
                    ];
                }
            }

            $period = CarbonPeriod::create($data['start_date'], $data['end_date']);
            $totalAssigned = 0;

            foreach ($period as $date) {
                $dayName = strtolower($date->format('l'));
                if (! in_array($dayName, $days, true)) {
                    continue;
                }
                if ($excludeWeekends && in_array($dayName, ['saturday', 'sunday'], true)) {
                    continue;
                }

                foreach ($refs as $ref) {
                    $this->upsertAssigneeShiftEntry(
                        $ref['type'],
                        $ref['id'],
                        $date->toDateString(),
                        $shift,
                        $overrideExisting
                    );
                    $totalAssigned++;
                }
            }

            return [
                'success' => true,
                'message' => 'Shift assignment completed successfully.',
                'total_assigned' => $totalAssigned,
            ];
        });
    }

    /**
     * Get data for the roster grid.
     */
    public function getGridData(int $year, int $month, int $weekIndex): array
    {
        // Calendar week range: always Monday -> Sunday
        // Week 1 starts from the Monday of the week that contains the first day of the month.
        $monthStart = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $firstWeekStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);

        $startDate = $firstWeekStart->copy()->addWeeks($weekIndex - 1)->startOfDay();
        $endDate = $startDate->copy()->addDays(6)->endOfDay();

        $employees = Employee::with('department')
            ->where('is_active', 1)
            ->shiftBasedWorkArrangement()
            ->orderBy('department_id')
            ->get();
        $outsourcedEmployees = OutsourcedEmployee::with('department')
            ->whereNull('deleted_at')
            ->orderBy('department_id')
            ->orderBy('full_name')
            ->get();

        $shiftEmployeeIds = $employees->pluck('id')->all();
        $outsourcedIds = $outsourcedEmployees->pluck('id')->all();

        // Build Department List
        $departments = [];
        $deptIds = $employees->pluck('department_id')
            ->merge($outsourcedEmployees->pluck('department_id'))
            ->unique();
        foreach ($deptIds as $did) {
            $deptId = (int) ($did ?? 0);
            $emp = $employees->firstWhere('department_id', $did);
            if (! $emp) {
                $emp = $outsourcedEmployees->firstWhere('department_id', $did);
            }
            $departments[] = [
                'id' => $deptId,
                'name' => $emp->department->name ?? 'Unassigned'
            ];
        }
        usort($departments, fn($a, $b) => strcmp($a['name'], $b['name']));

        // Build Employee Payload
        $empPayload = $employees->map(fn($e) => [
            'id' => 'employee:' . $e->id,
            'sourceType' => 'employee',
            'sourceId' => $e->id,
            'name' => $e->full_name,
            'departmentId' => (int) $e->department_id,
            'departmentName' => $e->department->name ?? 'Unassigned'
        ])->values();
        $outsourcedPayload = $outsourcedEmployees->map(fn($e) => [
            'id' => 'outsourced:' . $e->id,
            'sourceType' => 'outsourced',
            'sourceId' => $e->id,
            'name' => $e->full_name,
            'departmentId' => (int) ($e->department_id ?? 0),
            'departmentName' => $e->department->name ?? 'Unassigned'
        ])->values();
        $empPayload = $empPayload->concat($outsourcedPayload)->values()->all();

        $entriesQuery = ShiftRosterEntry::with('shift')
            ->whereBetween('roster_date', [$startDate->toDateString(), $endDate->toDateString()]);
        if ($shiftEmployeeIds !== [] || $outsourcedIds !== []) {
            $entriesQuery->where(function ($q) use ($shiftEmployeeIds, $outsourcedIds) {
                if ($shiftEmployeeIds !== []) {
                    $q->whereIn('employee_id', $shiftEmployeeIds);
                }
                if ($outsourcedIds !== []) {
                    $q->orWhereIn('outsourced_employee_id', $outsourcedIds);
                }
            });
        } else {
            $entriesQuery->whereRaw('1 = 0');
        }
        $entries = $entriesQuery->get();

        $shiftsOut = $entries->map(function($entry) {
            $shiftName = strtolower($entry->shift->name ?? '');
            $shiftType = 'general';
            if (str_contains($shiftName, 'morning')) {
                $shiftType = 'morning';
            } elseif (str_contains($shiftName, 'evening')) {
                $shiftType = 'evening';
            } elseif (str_contains($shiftName, 'night')) {
                $shiftType = 'night';
            }

            return [
                'rosterId' => $entry->id,
                'employeeId' => $entry->employee_id
                    ? ('employee:' . $entry->employee_id)
                    : ('outsourced:' . $entry->outsourced_employee_id),
                'employeeType' => $entry->employee_id ? 'employee' : 'outsourced',
                'sourceId' => $entry->employee_id ?: $entry->outsourced_employee_id,
                // Full date is used by the front-end to correctly align Mon->Sun columns.
                'rosterDate' => $entry->roster_date->toDateString(),
                // Kept for backward compatibility/fallbacks.
                'day' => (int) $entry->roster_date->format('d'),
                'shiftPlannerId' => $entry->shift_planner_id,
                'shiftType' => $shiftType,
                'timeStart' => $this->formatShiftTime($entry->start_time ?? $entry->shift->start_time),
                'timeEnd' => $this->formatShiftTime($entry->end_time ?? $entry->shift->end_time),
                'status' => $entry->status,
                'isCompensatory' => $entry->is_compensatory_earned
            ];
        })->all();

        return [
            'departments' => $departments,
            'employees' => $empPayload,
            'shifts' => $shiftsOut
        ];
    }

    private function formatShiftTime($value): string
    {
        if (!$value) return '00:00';
        return Carbon::parse($value)->format('H:i');
    }

    /**
     * @param array<int, string> $refs
     * @return array<int, array{type:string,id:int}>
     */
    private function parseAssigneeRefs(array $refs): array
    {
        $parsed = [];
        foreach ($refs as $ref) {
            [$type, $id] = array_pad(explode(':', (string) $ref, 2), 2, null);
            $id = (int) $id;
            if (! in_array($type, ['employee', 'outsourced'], true) || ! $id) {
                continue;
            }
            $parsed[] = ['type' => $type, 'id' => $id];
        }
        return $parsed;
    }

    /**
     * @return array<int, string>
     */
    private function collectBulkConflicts(array $refs, string $startDate, string $endDate, array $days): array
    {
        $employeeIds = collect($refs)->where('type', 'employee')->pluck('id')->all();
        $outsourcedIds = collect($refs)->where('type', 'outsourced')->pluck('id')->all();

        $conflictingEntries = ShiftRosterEntry::query()
            ->with(['employee:id,full_name', 'outsourcedEmployee:id,full_name'])
            ->whereBetween('roster_date', [$startDate, $endDate])
            ->where(function ($query) use ($employeeIds, $outsourcedIds) {
                if ($employeeIds !== []) {
                    $query->whereIn('employee_id', $employeeIds);
                }
                if ($outsourcedIds !== []) {
                    $query->orWhereIn('outsourced_employee_id', $outsourcedIds);
                }
            })
            ->get()
            ->filter(function ($entry) use ($days) {
                return in_array(strtolower($entry->roster_date->format('l')), $days, true);
            });

        $names = [];
        foreach ($conflictingEntries as $entry) {
            $name = $entry->employee?->full_name ?? $entry->outsourcedEmployee?->full_name;
            if ($name) {
                $names[] = $name;
            }
        }

        return array_values(array_unique($names));
    }

    private function upsertAssigneeShiftEntry(string $type, int $id, string $date, ShiftPlanner $shift, bool $overrideExisting): void
    {
        $basePayload = [
            'shift_planner_id' => $shift->id,
            'start_time' => $this->formatShiftTime($shift->start_time),
            'end_time' => $this->formatShiftTime($shift->end_time),
            'floor' => $shift->floor ?? null,
            'status' => 'pending',
        ];

        if ($type === 'outsourced') {
            if ($overrideExisting) {
                ShiftRosterEntry::updateOrCreate(
                    ['outsourced_employee_id' => $id, 'roster_date' => $date],
                    ['employee_id' => null] + $basePayload
                );
                return;
            }
            ShiftRosterEntry::firstOrCreate(
                ['outsourced_employee_id' => $id, 'roster_date' => $date, 'shift_planner_id' => $shift->id],
                ['employee_id' => null] + $basePayload
            );
            return;
        }

        if ($overrideExisting) {
            ShiftRosterEntry::updateOrCreate(
                ['employee_id' => $id, 'roster_date' => $date],
                ['outsourced_employee_id' => null] + $basePayload
            );
            return;
        }
        ShiftRosterEntry::firstOrCreate(
            ['employee_id' => $id, 'roster_date' => $date, 'shift_planner_id' => $shift->id],
            ['outsourced_employee_id' => null] + $basePayload
        );
    }
}
