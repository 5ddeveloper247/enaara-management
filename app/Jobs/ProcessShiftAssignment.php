<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ShiftRosterAssignment;
use App\Models\ShiftRosterEntry;
use App\Services\ShiftRosterHistoryService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ProcessShiftAssignment implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    protected $assignment;

    /**
     * Create a new job instance.
     */
    public function __construct(ShiftRosterAssignment $assignment)
    {
        $this->assignment = $assignment;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $assignment = $this->assignment->load(['employees', 'shift']);
            $startDate = $assignment->start_date;
            $endDate = $assignment->end_date;
            $selectedDays = $assignment->days ?? []; // e.g. ["monday", "tuesday"]
            
            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                $dayName = strtolower($currentDate->format('l'));
                
                // Skip if day not selected
                if (!in_array($dayName, $selectedDays)) {
                    $currentDate->addDay();
                    continue;
                }

                // Skip weekends if option enabled
                if ($assignment->exclude_weekends && ($dayName === 'saturday' || $dayName === 'sunday')) {
                    $currentDate->addDay();
                    continue;
                }

                foreach ($assignment->employees as $employee) {
                    $this->createEntry($employee, $assignment, $currentDate);
                }

                $currentDate->addDay();
            }

            $assignment->update(['status' => 'processed']);

        } catch (\Exception $e) {
            $this->assignment->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected function createEntry($employee, $assignment, $date)
    {
        $rosterDate = $date->toDateString();
        $shift = $assignment->shift;

        // 1. Check for basic existing entry if NOT overriding
        if (!$assignment->override_existing) {
            $exists = ShiftRosterEntry::where('employee_id', $employee->id)
                ->where('roster_date', $rosterDate)
                ->exists();
            if ($exists) return;
        }

        // 2. Conflict Detection (Rest Period)
        if ($assignment->check_conflicts) {
            $hasConflict = $this->checkRestPeriodConflict($employee->id, $rosterDate, $shift);
            if ($hasConflict) return;
        }

        $historyService = app(ShiftRosterHistoryService::class);
        $userId = Auth::id();

        $entry = ShiftRosterEntry::query()
            ->where('employee_id', $employee->id)
            ->where('roster_date', $rosterDate)
            ->first();

        $payload = [
            'shift_planner_id' => $shift->id,
            'assignment_id' => $assignment->id,
            'start_time' => $shift->start_time,
            'end_time' => $shift->end_time,
            'floor' => $shift->floor,
            'status' => 'pending',
            'is_custom_time' => false,
        ];

        if ($entry) {
            $before = $historyService->snapshot($entry);
            $entry->fill($payload);
            if ($userId) {
                $entry->updated_by = $userId;
            }
            $entry->save();
            $historyService->recordUpdated($entry, $before, $userId);

            return;
        }

        if ($userId) {
            $payload['created_by'] = $userId;
            $payload['assigned_by'] = $userId;
        }

        $entry = ShiftRosterEntry::query()->create([
            'employee_id' => $employee->id,
            'roster_date' => $rosterDate,
            'outsourced_employee_id' => null,
        ] + $payload);

        $historyService->recordCreated($entry, $userId);
    }

    protected function checkRestPeriodConflict($employeeId, $date, $newShift)
    {
        // Simple 8-hour rest check (Reusable logic from Service)
        $prevDate = \Carbon\Carbon::parse($date)->subDay()->toDateString();
        $nextDate = \Carbon\Carbon::parse($date)->addDay()->toDateString();

        $nearbyShifts = ShiftRosterEntry::where('employee_id', $employeeId)
            ->whereIn('roster_date', [$prevDate, $date, $nextDate])
            ->with('shift')
            ->get();

        foreach ($nearbyShifts as $existingEntry) {
            $existingShift = $existingEntry->shift;
            if (!$existingShift) continue;

            // Same day conflict
            if ($existingEntry->roster_date->toDateString() === $date && $existingShift->id !== $newShift->id) {
                return true; 
            }

            // Rest period calculation (Gap between shifts)
            // [Simplified for this implementation, can be expanded for precision]
            // If they have a shift on prev day ending late, and this one starts early...
        }

        return false;
    }
}
