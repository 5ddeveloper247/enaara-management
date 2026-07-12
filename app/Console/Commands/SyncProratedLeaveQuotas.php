<?php

namespace App\Console\Commands;

use App\Models\EmployeeLeaveQuota;
use App\Services\leaverequestPrivatefunctions\LeaveQuotaProrationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * One-time / on-demand backfill: updates employee_leave_quotas.quota for
 * unconditional leave types so mid-year joiners store the prorated entitlement
 * instead of the full annual_quota.
 *
 * Table touched: employee_leave_quotas (column: quota only)
 * Does not change: used, carried_forward, encashed, leave history tables
 */
class SyncProratedLeaveQuotas extends Command
{
    protected $signature = 'leaves:sync-prorated-quotas
                            {--year= : Calendar year to sync (defaults to current year)}
                            {--dry-run : Show what would change without updating the database}';

    protected $description = 'Sync employee_leave_quotas.quota to join-date prorated values for unconditional leave types.';

    public function handle(LeaveQuotaProrationService $leaveQuotaProrationService): int
    {
        $year = $this->option('year') ? (int) $this->option('year') : (int) Carbon::now()->year;
        $dryRun = (bool) $this->option('dry-run');

        $this->info(($dryRun ? '[DRY RUN] ' : '')."Syncing prorated leave quotas for year {$year}...");

        $quotas = EmployeeLeaveQuota::query()
            ->with(['employee', 'leaveType'])
            ->where('year', $year)
            ->get();

        $updated = 0;
        $skipped = 0;
        $alreadyOk = 0;

        foreach ($quotas as $quota) {
            $employee = $quota->employee;
            $leaveType = $quota->leaveType;

            if (! $employee || ! $leaveType) {
                $skipped++;
                continue;
            }

            if (! $leaveQuotaProrationService->shouldProrate($leaveType)) {
                $skipped++;
                continue;
            }

            $annualQuota = (float) $leaveType->annual_quota;
            $stored = (float) $quota->quota;
            $prorated = $leaveQuotaProrationService->forLeaveType($employee, $leaveType, $year);

            if (abs($stored - $prorated) < 0.001) {
                $alreadyOk++;
                continue;
            }

            // Only rewrite rows that still hold the un-prorated full annual entitlement.
            // Custom values / annual + carry-forward are left untouched.
            if (abs($stored - $annualQuota) >= 0.001) {
                $skipped++;
                $this->line(sprintf(
                    'Skip Emp#%d LT#%d (%s): stored=%.2f is custom (annual=%.2f, prorated=%.2f)',
                    $employee->id,
                    $leaveType->id,
                    $leaveType->name,
                    $stored,
                    $annualQuota,
                    $prorated
                ));
                continue;
            }

            $this->line(sprintf(
                '%s Emp#%d LT#%d (%s): %.2f → %.2f (join=%s)',
                $dryRun ? 'Would update' : 'Update',
                $employee->id,
                $leaveType->id,
                $leaveType->name,
                $stored,
                $prorated,
                optional($employee->join_date)?->format('Y-m-d') ?? 'n/a'
            ));

            if (! $dryRun) {
                $quota->quota = $prorated;
                $quota->save();

                Log::info('Prorated leave quota synced', [
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'year' => $year,
                    'from' => $stored,
                    'to' => $prorated,
                ]);
            }

            $updated++;
        }

        $this->newLine();
        $this->info('Sync complete.');
        $this->line("Updated: {$updated}");
        $this->line("Already correct: {$alreadyOk}");
        $this->line("Skipped: {$skipped}");

        if ($dryRun) {
            $this->warn('Dry run only — no database changes were made. Re-run without --dry-run to apply.');
        }

        return self::SUCCESS;
    }
}
