<?php

namespace App\Services;

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
}