<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmployeLeaveEntity;
use App\Models\EmployeeLeaveQuota;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcessDailyLeaves extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leaves:process-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates pending leave entities to taken and increments employee leave quotas.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->toDateString();
        
        $entities = EmployeLeaveEntity::where('status', 0) // pending
            ->where('leave_date', '<=', $today)
            ->with(['leaveType', 'employee'])
            ->get();

        if ($entities->isEmpty()) {
            $this->info("No pending leaves to process for $today.");
            return;
        }

        $this->info("Processing " . $entities->count() . " leave entities...");

        $processedCount = 0;

        foreach ($entities as $entity) {
            DB::beginTransaction();
            try {
                // 1. Mark entity as taken
                $entity->status = 1; // taken
                $entity->save();

                // 2. Determine the year for the quota
                $year = Carbon::parse($entity->leave_date)->year;

                // 3. Find or Create the Quota record
                $quota = EmployeeLeaveQuota::firstOrCreate(
                    [
                        'employee_id'   => $entity->employee_id,
                        'leave_type_id' => $entity->leave_type_id,
                        'year'          => $year,
                    ],
                    [
                        'department_id' => $entity->department_id,
                        'quota'         => optional($entity->leaveType)->annual_quota ?? 0,
                        'used'          => 0,
                    ]
                );

                // 4. Increment the used amount for billable leave only
                if ((float) $entity->duration > 0 && ($entity->counts_against_quota ?? true)) {
                    $quota->used += $entity->duration;
                }
                
                // Ensure department_id is synced if it was previously 0/null
                if (!$quota->department_id && $entity->department_id) {
                    $quota->department_id = $entity->department_id;
                }

                $quota->save();

                DB::commit();
                $processedCount++;
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Failed to process entity ID {$entity->id}: " . $e->getMessage());
            }
        }

        $this->info("Successfully processed $processedCount entities.");
    }
}
