<?php

namespace App\Services;

use App\Models\EmployeLeaveEntity;
use App\Models\EmployeLeaveRequest;
use App\Models\ShiftRosterEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CompensatoryLeaveBalanceService
{
    public const EXPIRY_DAYS = 45;

    private const REQUEST_ACTION_TYPES_FOR_BALANCE = [0, 1, 2];

    public function __construct(
        private readonly CompensatoryLeaveAwardService $compensatoryLeaveAwardService,
    ) {}

    public function expiryCutoffDate(?Carbon $asOf = null): Carbon
    {
        return ($asOf ?? Carbon::today())->copy()->subDays(self::EXPIRY_DAYS);
    }

    public function validEarnedDays(int $employeeId, ?Carbon $asOf = null): float
    {
        $asOf = ($asOf ?? Carbon::today())->copy()->startOfDay();
        $cutoff = $this->expiryCutoffDate($asOf);

        return (float) ShiftRosterEntry::query()
            ->where('employee_id', $employeeId)
            ->where('is_compensatory_earned', true)
            ->whereDate('roster_date', '>=', $cutoff->toDateString())
            ->whereDate('roster_date', '<=', $asOf->toDateString())
            ->count();
    }

    public function reservedDays(int $employeeId, int $leaveTypeId, ?int $year = null): float
    {
        $year = $year ?? (int) now()->year;

        $applied = $this->sumDedupedRequestDuration($employeeId, $leaveTypeId, $year, [0, 1]);
        $approved = $this->sumLeaveEntityDurationByStatus($employeeId, $leaveTypeId, $year, 0);
        $claimed = $this->sumLeaveEntityDurationByStatus($employeeId, $leaveTypeId, $year, 1);

        return $applied + $approved + $claimed;
    }

    public function remainingDays(int $employeeId, ?int $year = null, ?Carbon $asOf = null): float
    {
        $cplType = $this->compensatoryLeaveAwardService->resolveCompensatoryLeaveType();

        if ($cplType === null) {
            return 0.0;
        }

        $asOf = $asOf ?? Carbon::today();
        $year = $year ?? (int) $asOf->year;

        $validEarned = $this->validEarnedDays($employeeId, $asOf);
        $reserved = $this->reservedDays($employeeId, (int) $cplType->id, $year);

        return max(0, $validEarned - $reserved);
    }

    public function isCompensatoryLeaveTypeId(int $leaveTypeId): bool
    {
        $cplType = $this->compensatoryLeaveAwardService->resolveCompensatoryLeaveType();

        return $cplType !== null && (int) $cplType->id === $leaveTypeId;
    }

    public function resolveInsufficientBalanceMessage(
        int $employeeId,
        Carbon $startDate,
        float $requestedDays,
        float $availableOnLeaveDate
    ): string {
        $startDate = $startDate->copy()->startOfDay();
        $today = Carbon::today();
        $leaveDateLabel = $startDate->toDateString();
        $earnedOnLeaveDate = $this->validEarnedDays($employeeId, $startDate);
        $earnedToday = $this->validEarnedDays($employeeId, $today);

        if ($earnedToday > $earnedOnLeaveDate) {
            $firstEarnedAfterLeave = ShiftRosterEntry::query()
                ->where('employee_id', $employeeId)
                ->where('is_compensatory_earned', true)
                ->whereDate('roster_date', '>', $startDate->toDateString())
                ->whereDate('roster_date', '<=', $today->toDateString())
                ->min('roster_date');

            if ($firstEarnedAfterLeave !== null) {
                $earnedDate = Carbon::parse($firstEarnedAfterLeave)->format('Y-m-d');

                return "Compensatory leave cannot be applied for {$leaveDateLabel}. "
                    ."Your current balance includes time earned on {$earnedDate}, which is after this leave date. "
                    .'Compensatory leave can only be used from the date it was earned onward.';
            }
        }

        $hasExpiredEarnedBeforeLeave = ShiftRosterEntry::query()
            ->where('employee_id', $employeeId)
            ->where('is_compensatory_earned', true)
            ->whereDate('roster_date', '<', $this->expiryCutoffDate($startDate)->toDateString())
            ->whereDate('roster_date', '<=', $startDate->toDateString())
            ->exists();

        if ($hasExpiredEarnedBeforeLeave && $earnedOnLeaveDate < $requestedDays) {
            return "Insufficient compensatory leave balance for leave starting {$leaveDateLabel}. "
                .'Earned compensatory day(s) for that period have expired (valid for '
                .self::EXPIRY_DAYS.' days from the date earned). You have '.$availableOnLeaveDate
                ." day(s) available, but you are requesting {$requestedDays} day(s).";
        }

        if ($earnedOnLeaveDate >= $requestedDays && $availableOnLeaveDate < $requestedDays) {
            return "Insufficient compensatory leave balance for leave starting {$leaveDateLabel}. "
                .'You have '.$availableOnLeaveDate.' day(s) available after pending or approved usage, '
                ."but you are requesting {$requestedDays} day(s).";
        }

        return "Insufficient compensatory leave balance for leave starting {$leaveDateLabel}. "
            .'You have '.$availableOnLeaveDate.' day(s) available (earned days expire after '
            .self::EXPIRY_DAYS." days), but you are requesting {$requestedDays} day(s).";
    }

    private function sumDedupedRequestDuration(
        int $employeeId,
        int $leaveTypeId,
        int $year,
        array $statuses
    ): float {
        $base = EmployeLeaveRequest::query()
            ->where('from_employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->whereYear('start_date', $year)
            ->whereIn('action_type', self::REQUEST_ACTION_TYPES_FOR_BALANCE)
            ->whereIn('status', $statuses);

        $sub = (clone $base)
            ->selectRaw('MAX(duration) as block_days')
            ->groupBy('start_date', 'end_date');

        return (float) DB::query()->fromSub($sub, 'deduped_blocks')->sum('block_days');
    }

    private function sumLeaveEntityDurationByStatus(
        int $employeeId,
        int $leaveTypeId,
        int $year,
        int $entityStatus
    ): float {
        return (float) EmployeLeaveEntity::query()
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('status', $entityStatus)
            ->whereYear('leave_date', $year)
            ->sum('duration');
    }
}
