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
