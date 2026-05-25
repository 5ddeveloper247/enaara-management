<?php

namespace App\Console\Commands;

use App\Models\EmployeeLeaveQuota;
use App\Services\CompensatoryLeaveAwardService;
use App\Services\CompensatoryLeaveBalanceService;
use Illuminate\Console\Command;

class SyncCompensatoryLeaveQuotas extends Command
{
    protected $signature = 'compensatory:sync-quotas';

    protected $description = 'Align compensatory leave quota records with non-expired earned days (45-day validity).';

    public function handle(
        CompensatoryLeaveAwardService $compensatoryLeaveAwardService,
        CompensatoryLeaveBalanceService $compensatoryLeaveBalanceService,
    ): int {
        $cplType = $compensatoryLeaveAwardService->resolveCompensatoryLeaveType();

        if ($cplType === null) {
            $this->warn('Compensatory leave type not found. Nothing to sync.');

            return self::SUCCESS;
        }

        $quotas = EmployeeLeaveQuota::query()
            ->where('leave_type_id', $cplType->id)
            ->get();

        $updated = 0;

        foreach ($quotas as $quota) {
            $validEarned = (int) $compensatoryLeaveBalanceService->validEarnedDays((int) $quota->employee_id);
            $used = (int) round((float) $quota->used);
            $targetQuota = max($validEarned, $used);

            if ((int) round((float) $quota->quota) === $targetQuota) {
                continue;
            }

            $quota->quota = $targetQuota;
            $quota->save();
            $updated++;
        }

        $this->info("Compensatory quota sync complete. Updated {$updated} record(s).");

        return self::SUCCESS;
    }
}
