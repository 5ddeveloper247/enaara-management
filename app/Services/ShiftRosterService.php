<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\ShiftPlanner;
use App\Models\ShiftRoaster;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShiftRosterService
{
    /**
     * Store a newly created shift roster.
     */
    public function store(array $data)
    {
        DB::beginTransaction();

        try {
            $existingRoster = ShiftRoaster::where('employee_id', $data['employee_id'])
                ->whereDate('roster_date', $data['roster_date'])
                ->first();

            if ($existingRoster) {
                throw new \Exception('A shift is already assigned to this employee for the selected date.');
            }

            $shiftRoster = ShiftRoaster::create([
                'employee_id' => $data['employee_id'],
                'shift_planner_id' => $data['shift_planner_id'],
                'roster_date' => $data['roster_date'],
                'status' => $data['status'] ?? 1,
                'notes' => $data['notes'] ?? null,
                'assigned_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            DB::commit();
            return $shiftRoster;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Shift Roster Store Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing shift roster.
     */
    public function update(array $data, $id)
    {
        DB::beginTransaction();

        try {
            $shiftRoster = ShiftRoaster::findOrFail($id);

            $duplicateRoster = ShiftRoaster::where('employee_id', $data['employee_id'])
                ->whereDate('roster_date', $data['roster_date'])
                ->where('id', '!=', $id)
                ->first();

            if ($duplicateRoster) {
                throw new \Exception('A shift is already assigned to this employee for the selected date.');
            }

            $shiftRoster->update([
                'employee_id' => $data['employee_id'],
                'shift_planner_id' => $data['shift_planner_id'],
                'roster_date' => $data['roster_date'],
                'status' => $data['status'] ?? $shiftRoster->status,
                'notes' => $data['notes'] ?? $shiftRoster->notes,
                'updated_by' => auth()->id(),
            ]);

            DB::commit();
            return $shiftRoster;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Shift Roster Update Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a shift roster.
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $shiftRoster = ShiftRoaster::findOrFail($id);
            $shiftRoster->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Shift Roster Delete Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Bulk assign shifts to multiple employees across a date range.
     */
    public function bulkAssign(array $data): array
    {
        DB::beginTransaction();

        try {
            $employeeIds = $data['employee_ids'];
            $shiftPlannerId = $data['shift_planner_id'];

            $startDate = Carbon::parse($data['start_date']);
            $endDate = Carbon::parse($data['end_date']);

            $checkConflicts = (bool) ($data['check_conflicts'] ?? false);
            $overrideExisting = (bool) ($data['override_existing'] ?? false);
            $excludeWeekends = (bool) ($data['exclude_weekends'] ?? false);
            $notes = $data['notes'] ?? null;

            $createdCount = 0;
            $updatedCount = 0;
            $skippedCount = 0;
            $conflicts = [];

            $dates = $this->generateDateRange($startDate, $endDate, $excludeWeekends);

            foreach ($employeeIds as $employeeId) {
                foreach ($dates as $date) {
                    $rosterDate = $date->toDateString();

                    $existingRoster = ShiftRoaster::where('employee_id', $employeeId)
                        ->whereDate('roster_date', $rosterDate)
                        ->first();

                    if ($existingRoster) {
                        if ($checkConflicts && !$overrideExisting) {
                            $conflicts[] = [
                                'employee_id' => $employeeId,
                                'roster_date' => $rosterDate,
                                'existing_shift_planner_id' => $existingRoster->shift_planner_id,
                                'message' => 'Existing roster found for employee on this date.',
                            ];

                            $skippedCount++;
                            continue;
                        }

                        if ($overrideExisting) {
                            $existingRoster->update([
                                'shift_planner_id' => $shiftPlannerId,
                                'status' => 1,
                                'notes' => $notes,
                                'updated_by' => auth()->id(),
                            ]);

                            $updatedCount++;
                            continue;
                        }

                        $skippedCount++;
                        continue;
                    }

                    ShiftRoaster::create([
                        'employee_id' => $employeeId,
                        'shift_planner_id' => $shiftPlannerId,
                        'roster_date' => $rosterDate,
                        'status' => 1,
                        'notes' => $notes,
                        'assigned_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                    ]);

                    $createdCount++;
                }
            }

            DB::commit();

            return [
                'created_count' => $createdCount,
                'updated_count' => $updatedCount,
                'skipped_count' => $skippedCount,
                'conflicts' => $conflicts,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Shift Roster Bulk Assign Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate dates between start and end date.
     */
    private function generateDateRange(Carbon $startDate, Carbon $endDate, bool $excludeWeekends = false): array
    {
        $dates = [];
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            if ($excludeWeekends && $this->isWeekend($date)) {
                continue;
            }

            $dates[] = $date->copy();
        }

        return $dates;
    }

    /**
     * Check if given date is weekend.
     */
    private function isWeekend(Carbon $date): bool
    {
        return $date->isSaturday() || $date->isSunday();
    }

    public function getGridData(int $year, int $month, int $weekIndex): array
    {
        $daysInMonth = (int) Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $startDay = ($weekIndex - 1) * 7 + 1;
        $endDay = min($startDay + 6, $daysInMonth);
        if ($startDay > $daysInMonth) {
            return [
                'departments' => [],
                'employees'   => [],
                'shifts'      => [],
            ];
        }

        $startDate = Carbon::createFromDate($year, $month, $startDay)->startOfDay();
        $endDate = Carbon::createFromDate($year, $month, $endDay)->endOfDay();

        $employees = Employee::with('department')
            ->where('is_active', 1)
            ->orderBy('department_id')
            ->orderBy('full_name')
            ->get();

        $deptMap = [];
        foreach ($employees as $e) {
            $did = (int) ($e->department_id ?? 0);
            if (! isset($deptMap[$did])) {
                $deptMap[$did] = [
                    'id'   => $did,
                    'name' => $e->department->name ?? 'Unassigned',
                ];
            }
        }
        $departments = array_values($deptMap);
        usort($departments, fn ($a, $b) => strcmp($a['name'], $b['name']));

        $empPayload = $employees->map(function (Employee $e) {
            return [
                'id'           => $e->id,
                'name'         => $e->full_name,
                'departmentId' => (int) ($e->department_id ?? 0),
            ];
        })->values()->all();

        $employeeIds = $employees->pluck('id')->all();

        $rosters = ShiftRoaster::with(['shift'])
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('roster_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $shiftsOut = [];
        foreach ($rosters as $roster) {
            $sp = $roster->shift;
            if (! $sp) {
                continue;
            }
            $day = (int) $roster->roster_date->format('d');
            $shiftType = $this->classifyShiftType($sp);
            $shiftsOut[] = [
                'rosterId'       => $roster->id,
                'employeeId'     => $roster->employee_id,
                'day'            => $day,
                'shiftPlannerId' => $roster->shift_planner_id,
                'shiftType'      => $shiftType,
                'timeStart'      => $this->formatShiftTime($sp->start_time),
                'timeEnd'        => $this->formatShiftTime($sp->end_time),
                'checkIn'        => $this->formatShiftTime($sp->start_time),
                'checkOut'       => $this->formatShiftTime($sp->end_time),
                'floor'          => '',
                'lateCheckIn'    => false,
                'notes'          => $roster->notes ?? '',
            ];
        }

        return [
            'departments' => $departments,
            'employees'   => $empPayload,
            'shifts'      => $shiftsOut,
        ];
    }

    private function classifyShiftType(ShiftPlanner $shift): string
    {
        $name = strtolower($shift->name ?? '');
        $code = strtolower($shift->code ?? '');
        if (str_contains($name, 'morning') || str_contains($code, 'morning')) {
            return 'morning';
        }
        if (str_contains($name, 'evening') || str_contains($code, 'evening')) {
            return 'evening';
        }
        if (str_contains($name, 'night') || str_contains($code, 'night')) {
            return 'night';
        }
        $start = $shift->start_time;
        if ($start) {
            $h = (int) Carbon::parse($start)->format('G');
            if ($h < 12) {
                return 'morning';
            }
            if ($h < 17) {
                return 'evening';
            }
        }

        return 'night';
    }

    private function formatShiftTime($value): string
    {
        if (! $value) {
            return '09:00';
        }

        return Carbon::parse($value)->format('H:i');
    }
}