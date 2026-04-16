<?php

namespace App\Services;

use App\Models\ShiftPlanner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ShiftPlannerService
{
    /**
     * Store a newly created shift planner.
     */
    public function store(array $data)
    {
        DB::beginTransaction();

        try {
            $shiftPlannerData = [
                'name' => $data['name'],
                'code' => $data['code'] ?? null,
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'clock_in_window_minutes' => $data['clock_in_window_minutes'],
                'clock_out_window_minutes' => $data['clock_out_window_minutes'],
                'shift_duration_minutes' => $this->calculateShiftDuration($data['start_time'], $data['end_time']),
                'grace_period_minutes' => $data['grace_period_minutes'],
                'break_time_minutes' => $data['break_time_minutes'],
                'overtime_allowed' => $data['overtime_allowed'],
                'overtime_trigger_hours' => !empty($data['overtime_allowed']) ? ($data['overtime_trigger_hours'] ?? null) : null,
                'is_active' => $data['is_active'] ?? true,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ];

            $shiftPlanner = ShiftPlanner::create($shiftPlannerData);

            DB::commit();
            return $shiftPlanner;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Shift Planner Store Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing shift planner.
     */
    public function update(array $data, $id)
    {
        DB::beginTransaction();

        try {
            $shiftPlanner = ShiftPlanner::findOrFail($id);

            $shiftPlannerData = [
                'name' => $data['name'],
                'code' => $data['code'] ?? null,
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'clock_in_window_minutes' => $data['clock_in_window_minutes'],
                'clock_out_window_minutes' => $data['clock_out_window_minutes'],
                'shift_duration_minutes' => $this->calculateShiftDuration($data['start_time'], $data['end_time']),
                'grace_period_minutes' => $data['grace_period_minutes'],
                'break_time_minutes' => $data['break_time_minutes'],
                'overtime_allowed' => $data['overtime_allowed'],
                'overtime_trigger_hours' => !empty($data['overtime_allowed']) ? ($data['overtime_trigger_hours'] ?? null) : null,
                'is_active' => $data['is_active'] ?? $shiftPlanner->is_active,
                'updated_by' => auth()->id(),
            ];

            $shiftPlanner->update($shiftPlannerData);

            DB::commit();
            return $shiftPlanner;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Shift Planner Update Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a shift planner.
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $shiftPlanner = ShiftPlanner::findOrFail($id);
            $shiftPlanner->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Shift Planner Deletion Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Calculate shift duration in minutes.
     */
    private function calculateShiftDuration(string $startTime, string $endTime): int
    {
        $start = Carbon::createFromFormat('H:i', $startTime);
        $end = Carbon::createFromFormat('H:i', $endTime);

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return $start->diffInMinutes($end);
    }
}
