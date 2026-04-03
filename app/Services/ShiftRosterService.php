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
                'start_time' => $data['start_time'] ?? null,
                'end_time' => $data['end_time'] ?? null,
                'check_in' => $data['check_in'] ?? null,
                'check_out' => $data['check_out'] ?? null,
                'floor' => $data['floor'] ?? null,
                'late_check_in' => $data['late_check_in'] ?? false,
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
                'start_time' => isset($data['start_time']) ? $data['start_time'] : $shiftRoster->start_time,
                'end_time' => isset($data['end_time']) ? $data['end_time'] : $shiftRoster->end_time,
                'check_in' => isset($data['check_in']) ? $data['check_in'] : $shiftRoster->check_in,
                'check_out' => isset($data['check_out']) ? $data['check_out'] : $shiftRoster->check_out,
                'floor' => isset($data['floor']) ? $data['floor'] : $shiftRoster->floor,
                'late_check_in' => isset($data['late_check_in']) ? $data['late_check_in'] : $shiftRoster->late_check_in,
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
                    
                    // Advanced Conflict Check (Rest Period)
                    if ($checkConflicts) {
                        $conflictMessage = $this->checkRestPeriodConflict($employeeId, $rosterDate, $shiftPlannerId);
                        if ($conflictMessage && !$overrideExisting) {
                            $conflicts[] = [
                                'employee_id' => $employeeId,
                                'roster_date' => $rosterDate,
                                'message' => $conflictMessage,
                            ];
                            $skippedCount++;
                            continue;
                        }
                    }

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
                                // Reset override fields to shift defaults on bulk override
                                'start_time' => null,
                                'end_time' => null,
                                'check_in' => null,
                                'check_out' => null,
                                'floor' => null,
                                'late_check_in' => false,
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
            $shiftsOut[] = [
                'rosterId'       => $roster->id,
                'employeeId'     => $roster->employee_id,
                'day'            => $day,
                'shiftPlannerId' => $roster->shift_planner_id,
                'timeStart'      => $roster->start_time ? $this->formatShiftTime($roster->start_time) : $this->formatShiftTime($sp->start_time),
                'timeEnd'        => $roster->end_time ? $this->formatShiftTime($roster->end_time) : $this->formatShiftTime($sp->end_time),
                'checkIn'        => $roster->check_in ? $this->formatShiftTime($roster->check_in) : $this->formatShiftTime($sp->start_time),
                'checkOut'       => $roster->check_out ? $this->formatShiftTime($roster->check_out) : $this->formatShiftTime($sp->end_time),
                'floor'          => $roster->floor ?? '',
                'lateCheckIn'    => (bool) ($roster->late_check_in ?? false),
                'notes'          => $roster->notes ?? '',
            ];
        }

        return [
            'departments' => $departments,
            'employees'   => $empPayload,
            'shifts'      => $shiftsOut,
        ];
    }

    private function formatShiftTime($value): string
    {
        if (! $value) {
            return '09:00';
        }

        return Carbon::parse($value)->format('H:i');
    }

    /**
     * Check for rest period conflicts (minimum 8 hours between shifts).
     */
    private function checkRestPeriodConflict(int $employeeId, string $date, int $newShiftId): ?string
    {
        $newShift = ShiftPlanner::find($newShiftId);
        if (!$newShift) return null;

        $newStartTimeRaw = $newShift->getRawOriginal('start_time');
        $newEndTimeRaw = $newShift->getRawOriginal('end_time');
        
        if (!$newStartTimeRaw || !$newEndTimeRaw) return null;

        $newStart = Carbon::parse($date . ' ' . $newStartTimeRaw);
        $newEnd = Carbon::parse($date . ' ' . $newEndTimeRaw);
        if ($newEnd->lessThan($newStart)) {
            $newEnd->addDay();
        }

        // Check previous day shift
        $prevDate = Carbon::parse($date)->subDay()->toDateString();
        $prevRoster = ShiftRoaster::with('shift')
            ->where('employee_id', $employeeId)
            ->whereDate('roster_date', $prevDate)
            ->first();

        if ($prevRoster && ($prevRoster->shift || $prevRoster->start_time)) {
            $prevEndStr = $prevRoster->end_time ? $prevRoster->getRawOriginal('end_time') : ($prevRoster->shift ? $prevRoster->shift->getRawOriginal('end_time') : null);
            $prevStartStr = $prevRoster->start_time ? $prevRoster->getRawOriginal('start_time') : ($prevRoster->shift ? $prevRoster->shift->getRawOriginal('start_time') : null);
            
            if ($prevEndStr && $prevStartStr) {
                $prevEnd = Carbon::parse($prevDate . ' ' . $prevEndStr);
                $prevStart = Carbon::parse($prevDate . ' ' . $prevStartStr);
                
                if ($prevEnd->lessThan($prevStart)) {
                    $prevEnd->addDay();
                }

                if ($prevEnd->diffInHours($newStart, false) < 8) {
                    return "Insufficient rest period (less than 8 hours) after previous day's shift.";
                }
            }
        }

        // Check same day shift (if any other than existing)
        $sameDayRoster = ShiftRoaster::where('employee_id', $employeeId)
            ->whereDate('roster_date', $date)
            ->first();
            
        if ($sameDayRoster && $sameDayRoster->shift_planner_id != $newShiftId) {
             return "Employee already assigned to another shift on this day.";
        }

        // Check next day shift
        $nextDate = Carbon::parse($date)->addDay()->toDateString();
        $nextRoster = ShiftRoaster::with('shift')
            ->where('employee_id', $employeeId)
            ->whereDate('roster_date', $nextDate)
            ->first();

        if ($nextRoster && ($nextRoster->shift || $nextRoster->start_time)) {
            $nextStartStr = $nextRoster->start_time ? $nextRoster->getRawOriginal('start_time') : ($nextRoster->shift ? $nextRoster->shift->getRawOriginal('start_time') : null);
            
            if ($nextStartStr) {
                $nextStart = Carbon::parse($nextDate . ' ' . $nextStartStr);
                if ($newEnd->diffInHours($nextStart, false) < 8) {
                    return "Insufficient rest period (less than 8 hours) before next day's shift.";
                }
            }
        }

        return null;
    }
}