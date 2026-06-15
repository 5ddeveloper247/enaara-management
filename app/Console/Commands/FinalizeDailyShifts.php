<?php

namespace App\Console\Commands;

use App\Services\CompensatoryLeaveAwardService;
use App\Services\ShiftRosterFinalizeService;
use Illuminate\Console\Command;

class FinalizeDailyShifts extends Command
{
    protected $signature = 'shift-roster:finalize
                            {--date= : Finalize only this date (YYYY-MM-DD). Omit to finalize all pending shifts through today}';

    protected $description = 'Finalize pending shift roster entries and award compensatory leave for holiday/off-day work';

    public function handle(ShiftRosterFinalizeService $finalizeService): int
    {
        $singleDate = $this->option('date');

        if ($singleDate) {
            $this->info("Finalizing shifts for {$singleDate}...");
            $pendingCount = $finalizeService->pendingCountForDate($singleDate);
        } else {
            $throughDate = now()->toDateString();
            $this->info("Finalizing all pending shifts through {$throughDate}...");
            $pendingCount = $finalizeService->pendingCountThrough($throughDate);
        }

        if ($pendingCount === 0) {
            $this->info('No pending internal employee shifts to process.');

            return self::SUCCESS;
        }

        if (app(CompensatoryLeaveAwardService::class)->resolveCompensatoryLeaveType() === null) {
            $this->error('Compensatory Leave type not found. Create an active leave type with code CPL (case-insensitive).');

            return self::FAILURE;
        }

        $stats = $singleDate
            ? $finalizeService->finalizeForDate($singleDate)
            : $finalizeService->finalizeAllPendingThrough(now()->toDateString());

        $this->info("Processed: {$stats['processed']}");
        $this->info("Compensatory leave awarded: {$stats['cpl_awarded']}");
        $this->info("Finalized as used: {$stats['used']}");
        $this->info("Skipped (off/cancelled/non-work): {$stats['skipped']}");

        foreach ($stats['errors'] as $entryId => $message) {
            $this->error("Entry #{$entryId}: {$message}");
        }

        return $stats['errors'] === [] ? self::SUCCESS : self::FAILURE;
    }
}
