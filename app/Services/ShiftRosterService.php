<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\ShiftPlanner;
use App\Models\ShiftRosterAssignment;
use App\Models\ShiftRosterEntry;
use App\Jobs\ProcessShiftAssignment;
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
        DB::beginTransaction();
        try {
            // Create a single-day assignment record
            $assignment = ShiftRosterAssignment::create([
                'shift_planner_id' => $data['shift_planner_id'],
                'start_date' => $data['roster_date'],
                'end_date' => $data['roster_date'],
                'days' => [strtolower(Carbon::parse($data['roster_date'])->format('l'))],
                'assign_mode' => 'custom',
                'check_conflicts' => true,
                'override_existing' => true,
                'status' => 'pending'
            ]);

            $assignment->employees()->attach($data['employee_id']);

            DB::commit();

            // Dispatch job to generate entry
            ProcessShiftAssignment::dispatch($assignment);

            return $assignment;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Shift Roster Store Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing shift roster entry.
     */
    public function update(array $data, $id)
    {
        $entry = ShiftRosterEntry::findOrFail($id);
        
        $entry->update([
            'shift_planner_id' => $data['shift_planner_id'],
            'roster_date' => $data['roster_date'],
            'start_time' => $data['start_time'] ?? $entry->start_time,
            'end_time' => $data['end_time'] ?? $entry->end_time,
            'floor' => $data['floor'] ?? $entry->floor,
            'status' => $data['status'] ?? $entry->status,
        ]);

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
        DB::beginTransaction();

        try {
            // 1. Pre-validation: Check for existing entries in the range if not overriding
            if (($data['check_conflicts'] ?? 1) == 1 && !($data['override_existing'] ?? 0)) {
                $days = $data['days'] ?? [];
                $conflictingEmployees = ShiftRosterEntry::whereIn('employee_id', $data['employee_ids'])
                    ->whereBetween('roster_date', [$data['start_date'], $data['end_date']])
                    ->with('employee')
                    ->get()
                    ->filter(function($entry) use ($days) {
                        return in_array(strtolower($entry->roster_date->format('l')), $days);
                    })
                    ->pluck('employee.full_name')
                    ->unique();

                if ($conflictingEmployees->isNotEmpty()) {
                    return [
                        'success' => false,
                        'message' => 'Some employees already have shifts assigned on the selected days: ' . $conflictingEmployees->implode(', '),
                        'conflicts' => $conflictingEmployees->values()->all()
                    ];
                }
            }

            // 2. Create the Assignment record (The "Request")
            $assignment = ShiftRosterAssignment::create([
                'shift_planner_id' => $data['shift_planner_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'days' => $data['days'] ?? [],
                'assign_mode' => $data['assign_mode'] ?? 'default',
                'check_conflicts' => ($data['check_conflicts'] ?? 1) == 1,
                'override_existing' => ($data['override_existing'] ?? 0) == 1,
                'exclude_weekends' => ($data['exclude_weekends'] ?? 0) == 1,
                'status' => 'pending'
            ]);

            // 3. Link employees
            if (!empty($data['employee_ids'])) {
                $assignment->employees()->attach($data['employee_ids']);
            }

            DB::commit();

            // 3. Dispatch the background job to process the range
            ProcessShiftAssignment::dispatch($assignment);

            return [
                'success' => true,
                'message' => 'Shift assignment request submitted and is being processed in the background.',
                'assignment_id' => $assignment->id
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Shift Roster Bulk Assign Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get data for the roster grid.
     */
    public function getGridData(int $year, int $month, int $weekIndex): array
    {
        $daysInMonth = (int) Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $startDay = ($weekIndex - 1) * 7 + 1;
        $endDay = min($startDay + 6, $daysInMonth);
        
        if ($startDay > $daysInMonth) {
            return ['departments' => [], 'employees' => [], 'shifts' => []];
        }

        $startDate = Carbon::createFromDate($year, $month, $startDay)->startOfDay();
        $endDate = Carbon::createFromDate($year, $month, $endDay)->endOfDay();

        $employees = Employee::with('department')
            ->where('is_active', 1)
            ->orderBy('department_id')
            ->get();

        // Build Department List
        $departments = [];
        $deptIds = $employees->pluck('department_id')->unique();
        foreach ($deptIds as $did) {
            $emp = $employees->firstWhere('department_id', $did);
            $departments[] = [
                'id' => (int) $did,
                'name' => $emp->department->name ?? 'Unassigned'
            ];
        }
        usort($departments, fn($a, $b) => strcmp($a['name'], $b['name']));

        // Build Employee Payload
        $empPayload = $employees->map(fn($e) => [
            'id' => $e->id,
            'name' => $e->full_name,
            'departmentId' => (int) $e->department_id,
            'departmentName' => $e->department->name ?? 'Unassigned'
        ])->values()->all();

        // Fetch Entries
        $entries = ShiftRosterEntry::with('shift')
            ->whereBetween('roster_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $shiftsOut = $entries->map(fn($entry) => [
            'rosterId' => $entry->id,
            'employeeId' => $entry->employee_id,
            'day' => (int) $entry->roster_date->format('d'),
            'shiftPlannerId' => $entry->shift_planner_id,
            'timeStart' => $this->formatShiftTime($entry->start_time ?? $entry->shift->start_time),
            'timeEnd' => $this->formatShiftTime($entry->end_time ?? $entry->shift->end_time),
            'status' => $entry->status,
            'isCompensatory' => $entry->is_compensatory_earned
        ])->all();

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
}