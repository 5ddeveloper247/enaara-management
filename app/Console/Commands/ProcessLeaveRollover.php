<?php

namespace App\Console\Commands;

use App\Models\EmployeeLeaveQuota;
use App\Models\LeaveTypeEncashmentRule;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessLeaveRollover extends Command
{
    protected $signature = 'leave:rollover
                            {--year= : The year to process the rollover for. Defaults to previous year}
                            {--force : Re-process even if next year quota already exists}';

    protected $description = 'Process End of Year Leave Carry Forward and Encashment logic.';

    public function handle(): int
    {
        $year = $this->option('year') ? (int) $this->option('year') : Carbon::now()->subYear()->year;
        $nextYear = $year + 1;
        $force = (bool) $this->option('force');

        $this->info("Starting Leave Rollover Process for the year {$year} -> {$nextYear}");

        $quotas = EmployeeLeaveQuota::with(['employee.role', 'leaveType.setting'])
            ->where('year', $year)
            ->get();

        $processedCount = 0;
        $skippedCount = 0;
        $totalCarriedForward = 0;
        $totalEncashed = 0;
        $totalLapsed = 0;

        DB::beginTransaction();

        try {
            foreach ($quotas as $quota) {
                if (! $quota->employee || ! $quota->employee->is_active) {
                    continue;
                }

                $leaveType = $quota->leaveType;
                $setting = $leaveType?->setting;

                if (! $leaveType || ! $setting) {
                    continue;
                }

                if (! $force && $this->nextYearQuotaExists($quota, $nextYear)) {
                    $skippedCount++;
                    continue;
                }

                $remainingBalance = max(0, (float) $quota->remaining_balance);
                $carriedForward = $this->calculateCarriedForward($remainingBalance, $setting);
                $remainingAfterCF = max(0, $remainingBalance - $carriedForward);
                $encashedDays = $this->calculateEncashment(
                    $quota,
                    $setting,
                    $leaveType->id,
                    $remainingAfterCF,
                    $year
                );
                $lapsedDays = max(0, $remainingAfterCF - $encashedDays);

                $quota->update([
                    'carried_forward' => $carriedForward,
                    'encashed' => $encashedDays,
                ]);

                if ($leaveType->is_active) {
                    $newAnnualQuota = (float) $leaveType->annual_quota;

                    EmployeeLeaveQuota::updateOrCreate(
                        [
                            'employee_id' => $quota->employee_id,
                            'leave_type_id' => $quota->leave_type_id,
                            'year' => $nextYear,
                        ],
                        [
                            'department_id' => $quota->department_id,
                            'quota' => $newAnnualQuota + $carriedForward,
                            'used' => 0,
                            'carried_forward' => 0,
                            'encashed' => 0,
                        ]
                    );

                    $processedCount++;
                    $totalCarriedForward += $carriedForward;
                    $totalEncashed += $encashedDays;
                    $totalLapsed += $lapsedDays;

                    Log::info('Leave rollover processed', [
                        'year' => $year,
                        'employee_id' => $quota->employee_id,
                        'leave_type_id' => $quota->leave_type_id,
                        'remaining' => $remainingBalance,
                        'carried_forward' => $carriedForward,
                        'encashed' => $encashedDays,
                        'lapsed' => $lapsedDays,
                    ]);

                    $this->line(sprintf(
                        'Emp#%d LT#%d | rem=%.2f cf=%.2f encash=%.2f lapsed=%.2f',
                        $quota->employee_id,
                        $quota->leave_type_id,
                        $remainingBalance,
                        $carriedForward,
                        $encashedDays,
                        $lapsedDays
                    ));
                }
            }

            DB::commit();

            $this->info('Rollover Process Completed Successfully.');
            $this->line("Employees Processed: {$processedCount}");
            $this->line("Skipped (already processed): {$skippedCount}");
            $this->line("Total Days Carried Forward: {$totalCarriedForward}");
            $this->line("Total Days Encashed: {$totalEncashed}");
            $this->line("Total Days Lapsed: {$totalLapsed}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Leave rollover failed', [
                'year' => $year,
                'exception' => $e->getMessage(),
            ]);

            $this->error('Rollover Failed: ' . $e->getMessage());

            return self::FAILURE;
        }
    }

    private function nextYearQuotaExists(EmployeeLeaveQuota $quota, int $nextYear): bool
    {
        return EmployeeLeaveQuota::where('employee_id', $quota->employee_id)
            ->where('leave_type_id', $quota->leave_type_id)
            ->where('year', $nextYear)
            ->exists();
    }

    private function calculateCarriedForward(float $remainingBalance, $setting): float
    {
        if ($remainingBalance <= 0) {
            return 0;
        }

        if ($setting->carry_forward === 'yes') {
            return min($remainingBalance, (float) $setting->max_carry_forward_days);
        }

        if ($setting->carry_forward === 'as_earned') {
            return $remainingBalance;
        }

        return 0;
    }

    private function calculateEncashment(
        EmployeeLeaveQuota $quota,
        $setting,
        int $leaveTypeId,
        float $remainingAfterCF,
        int $year
    ): float {
        if ($remainingAfterCF <= 0) {
            return 0;
        }

        if (! in_array($setting->encashment_allowed, ['yes', 'as_per_policy'], true)) {
            return 0;
        }

        if ($setting->encashment_allowed === 'yes' && $setting->encashment_rule === 'full') {
            return $remainingAfterCF;
        }

        return $this->calculateRuleBasedEncashment($quota, $leaveTypeId, $remainingAfterCF, $year);
    }

    private function calculateRuleBasedEncashment(
        EmployeeLeaveQuota $quota,
        int $leaveTypeId,
        float $remainingAfterCF,
        int $year
    ): float {
        $employee = $quota->employee;
        $roleLevelId = $employee->role?->role_level_id;

        if (! $roleLevelId || ! $employee->join_date) {
            $this->warn("Emp#{$employee->id} LT#{$leaveTypeId}: encashment skipped (missing role or join date).");

            return 0;
        }

        $joinDate = Carbon::parse($employee->join_date);
        $yearEnd = Carbon::create($year, 12, 31)->endOfDay();

        if ($joinDate->gt($yearEnd)) {
            $this->warn("Emp#{$employee->id} LT#{$leaveTypeId}: encashment skipped (joined after year end).");

            return 0;
        }

        $totalServiceMonths = (int) $joinDate->diffInMonths($yearEnd);

        $rule = LeaveTypeEncashmentRule::where('leave_type_id', $leaveTypeId)
            ->where('role_level_id', $roleLevelId)
            ->where('service_months', '<=', $totalServiceMonths)
            ->orderBy('service_months', 'desc')
            ->first();

        if (! $rule) {
            $this->warn("Emp#{$employee->id} LT#{$leaveTypeId}: no encashment rule matched (service={$totalServiceMonths}m, role={$roleLevelId}).");

            return 0;
        }

        return min($remainingAfterCF, (float) $rule->max_forward_days);
    }
}
