<?php

namespace App\Console\Commands;

use App\Services\CompensatoryLeaveAwardService;
use App\Services\ShiftRosterFinalizeService;
use Illuminate\Console\Command;

class FinalizeDailyShifts extends Command
{
    protected $signature = 'shift-roster:finalize {--date= : The date to finalize (YYYY-MM-DD)}';

    protected $description = 'Finalize pending shift roster entries and award compensatory leave for holiday/off-day work';

    public function handle(ShiftRosterFinalizeService $finalizeService): int
    {
        $dateStr = $this->option('date') ?: now()->toDateString();
        $this->info("Finalizing shifts for {$dateStr}...");

        if ($finalizeService->pendingCountForDate($dateStr) === 0) {
            $this->info('No pending internal employee shifts to process.');

            return self::SUCCESS;
        }

        if (app(CompensatoryLeaveAwardService::class)->resolveCompensatoryLeaveType() === null) {
            $this->error('Compensatory Leave type not found. Create an active leave type with code CPL (case-insensitive).');

            return self::FAILURE;
        }

        $stats = $finalizeService->finalizeForDate($dateStr);

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
