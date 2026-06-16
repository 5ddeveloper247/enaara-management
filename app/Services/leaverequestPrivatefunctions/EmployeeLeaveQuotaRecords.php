<?php

namespace App\Services\leaverequestPrivatefunctions;

use App\Models\Employee;
use App\Models\EmployeeLeaveQuota;
use App\Models\EmployeLeaveEntity;
use App\Models\EmployeLeaveRequest;
use App\Models\LeaveBalanceAdjustment;
use App\Models\LeaveType;
use App\Services\CompensatoryLeaveBalanceService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeLeaveQuotaRecords
{
    public function __construct(
        private CompensatoryLeaveBalanceService $compensatoryLeaveBalanceService,
    ) {}

    private const MASTER_ACTION_TYPES = [0, 2];

    private const REQUEST_ACTION_TYPES_FOR_BALANCE = [0, 1, 2];

    public function buildSummaryForEmployee(int $employeeId, Collection $leaveTypes, ?int $year = null): array
    {
        if ($leaveTypes->isEmpty()) {
            return [];
        }

        $year = $year ?? (int) now()->year;
        $context = $this->loadQuotaContext($employeeId, $leaveTypes->pluck('id')->all(), $year);
        $summary = [];
        foreach ($leaveTypes as $type) {
            $leaveTypeId = (int) $type->id;

            if ($this->compensatoryLeaveBalanceService->isCompensatoryLeaveTypeId($leaveTypeId)) {
                $maxAllowed = $this->compensatoryLeaveBalanceService->validEarnedDays($employeeId);
                $reserved = $this->compensatoryUsageDays($employeeId, $leaveTypeId, $year);
                $remaining = max(0, $maxAllowed - $reserved);
                $applied = $this->sumDedupedRequestDuration($employeeId, $leaveTypeId, $year, [0, 1], null);
                $approved = $this->sumLeaveEntityDurationByStatus($employeeId, $leaveTypeId, $year, 0);
                $claimed = $this->sumLeaveEntityDurationByStatus($employeeId, $leaveTypeId, $year, 1);
            } else {
                $maxAllowed = $this->maxAllowedDays($context, $leaveTypeId, (float) $type->annual_quota);

                $applied = $this->sumDedupedRequestDuration($employeeId, $leaveTypeId, $year, [0, 1], null);
                $approved = $this->sumLeaveEntityDurationByStatus($employeeId, $leaveTypeId, $year, 0);
                $claimed = $this->sumLeaveEntityDurationByStatus($employeeId, $leaveTypeId, $year, 1);

                $reserved = $applied + $approved + $claimed;
                $remaining = max(0, $maxAllowed - $reserved);
            }

            $summary[] = [
                'id' => $type->id,
                'type' => $type->name,
                'leave_condition' => $type->leave_condition ?? 'unconditional',
                'total' => $maxAllowed,
                'applied' => $applied,
                'approved' => $approved,
                'claimed' => $claimed,
                'used' => $reserved,
                'remaining' => $remaining,
                'percentage' => $maxAllowed > 0
                    ? min(100, (int) round(($reserved / $maxAllowed) * 100))
                    : 0,
            ];
        }

        return $summary;
    }

    public function earnedLeaveRemainingDays(int $employeeId, int $leaveTypeId, ?int $year = null): float
    {
        if ($this->compensatoryLeaveBalanceService->isCompensatoryLeaveTypeId($leaveTypeId)) {
            return $this->compensatoryLeaveBalanceService->remainingDays($employeeId, $year);
        }

        $year = $year ?? (int) now()->year;
        $context = $this->loadQuotaContext($employeeId, [$leaveTypeId], $year);

        $quota = $context['quotas'][$leaveTypeId] ?? null;
        $maxAllowed = $quota
            ? (float) $quota->quota + (float) ($context['adjustments'][$leaveTypeId] ?? 0)
            : 0.0;

        $reserved = $this->compensatoryUsageDays($employeeId, $leaveTypeId, $year);

        return max(0, $maxAllowed - $reserved);
    }

    public function compensatoryUsageDays(int $employeeId, int $leaveTypeId, ?int $year = null): float
    {
        $year = $year ?? (int) now()->year;

        $applied = $this->sumDedupedRequestDuration($employeeId, $leaveTypeId, $year, [0, 1], null);
        $approved = $this->sumLeaveEntityDurationByStatus($employeeId, $leaveTypeId, $year, 0);
        $claimed = $this->sumLeaveEntityDurationByStatus($employeeId, $leaveTypeId, $year, 1);

        return $applied + $approved + $claimed;
    }

    private function sumDedupedRequestDuration(
        int $employeeId,
        int $leaveTypeId,
        int $year,
        array $statuses,
        ?string $compareEndDate,
        ?string $endDateOperator = null
    ): float {
        $base = EmployeLeaveRequest::query()
            ->where('from_employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->whereYear('start_date', $year)
            ->whereIn('action_type', self::REQUEST_ACTION_TYPES_FOR_BALANCE)
            ->whereIn('status', $statuses);

        if ($compareEndDate !== null && $endDateOperator === '>=') {
            $base->whereDate('end_date', '>=', $compareEndDate);
        }

        if ($compareEndDate !== null && $endDateOperator === '<') {
            $base->whereDate('end_date', '<', $compareEndDate);
        }

        $sub = (clone $base)
            ->selectRaw('MAX(GREATEST(duration - COALESCE(exempt_days, 0), 0)) as block_days')
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

    public function buildBalanceLookupForRequests(Collection $requests): array
    {
        if ($requests->isEmpty()) {
            return [];
        }

        $employeeIds = $requests->pluck('from_employee_id')->unique()->filter()->values()->all();
        $leaveTypeIds = $requests->pluck('leave_type_id')->unique()->filter()->values()->all();
        $years = $requests
            ->map(fn ($row) => (int) Carbon::parse($row->start_date)->year)
            ->unique()
            ->values()
            ->all();

        if ($employeeIds === [] || $leaveTypeIds === [] || $years === []) {
            return [];
        }

        $quotas = EmployeeLeaveQuota::query()
            ->whereIn('employee_id', $employeeIds)
            ->whereIn('leave_type_id', $leaveTypeIds)
            ->whereIn('year', $years)
            ->get();

        $adjustments = $this->loadAdjustmentTotals($employeeIds, $leaveTypeIds, $years);
        $quotaByKey = [];

        foreach ($quotas as $quota) {
            $quotaByKey[$this->rowKey((int) $quota->employee_id, (int) $quota->leave_type_id, (int) $quota->year)] = $quota;
        }

        // Batch-load all approved-but-not-yet-claimed entities (status=0) for all relevant employees/types/years
        $approvedEntities = EmployeLeaveEntity::query()
            ->selectRaw('employee_id, leave_type_id, YEAR(leave_date) as leave_year, SUM(duration) as total_approved')
            ->whereIn('employee_id', $employeeIds)
            ->whereIn('leave_type_id', $leaveTypeIds)
            ->whereIn(DB::raw('YEAR(leave_date)'), $years)
            ->where('status', 0) // 0 = approved/pending entity (not yet claimed by daily cron)
            ->groupBy('employee_id', 'leave_type_id', DB::raw('YEAR(leave_date)'))
            ->get()
            ->keyBy(fn ($row) => $this->rowKey((int) $row->employee_id, (int) $row->leave_type_id, (int) $row->leave_year));

        $lookup = [];

        foreach ($requests as $request) {
            $employeeId = (int) $request->from_employee_id;
            $leaveTypeId = (int) $request->leave_type_id;
            $year = (int) Carbon::parse($request->start_date)->year;
            $key = $this->rowKey($employeeId, $leaveTypeId, $year);

            $fallback = (float) (optional($request->leaveType)->annual_quota ?? 0);
            $quota = $quotaByKey[$key] ?? null;
            $adjustment = $adjustments[$key] ?? 0.0;

            $maxAllowed = $quota
                ? (float) $quota->quota + $adjustment
                : $fallback;

            // quota.used = already claimed days (written by ProcessDailyLeaves cron, entity status=1)
            $claimed = $quota ? (float) $quota->used : 0.0;

            // Approved but not yet claimed (entity status=0) — deducted immediately on approval
            $approved = (float) ($approvedEntities[$key]->total_approved ?? 0);

            $used = $claimed + $approved;
            $remaining = max(0, $maxAllowed - $used);

            $lookup[$key] = $remaining . ' / ' . $maxAllowed;
        }

        return $lookup;
    }

    public function getBalanceForRequest(
        int $employeeId,
        int $leaveTypeId,
        int $year,
        float $annualQuotaFallback = 0
    ): array {
        $context = $this->loadQuotaContext($employeeId, [$leaveTypeId], $year);
        $maxAllowed = $this->maxAllowedDays($context, $leaveTypeId, $annualQuotaFallback);
        $used = $this->usedDays($context, $leaveTypeId);
        $remaining = max(0, $maxAllowed - $used);

        return [
            'remaining' => $remaining,
            'max_allowed' => $maxAllowed,
            'display' => $remaining . ' / ' . $maxAllowed,
        ];
    }

    public function assertCanRequestDays(
        Employee $employee,
        int $leaveTypeId,
        Carbon $startDate,
        float $requestedDays
    ): void {
        $year = (int) $startDate->year;

        if ($this->compensatoryLeaveBalanceService->isCompensatoryLeaveTypeId($leaveTypeId)) {
            $remaining = $this->compensatoryLeaveBalanceService->remainingDays(
                $employee->id,
                $year,
                $startDate->copy()->startOfDay()
            );

            if ($requestedDays > $remaining) {
                throw ValidationException::withMessages([
                    'leave_type_id' => $this->compensatoryLeaveBalanceService->resolveInsufficientBalanceMessage(
                        $employee->id,
                        $startDate,
                        $requestedDays,
                        $remaining
                    ),
                ]);
            }

            return;
        }

        $leaveType = LeaveType::query()
            ->whereKey($leaveTypeId)
            ->value('annual_quota');

        $context = $this->loadQuotaContext($employee->id, [$leaveTypeId], $year, true);

        $maxAllowed = $this->maxAllowedDays($context, $leaveTypeId, (float) ($leaveType ?? 0));
        $alreadyClaimed = $this->usedDays($context, $leaveTypeId);
        $alreadyClaimed += (float) ($context['pending'][$leaveTypeId] ?? 0);

        if (($alreadyClaimed + $requestedDays) > $maxAllowed) {
            $remaining = max(0, $maxAllowed - $alreadyClaimed);

            throw ValidationException::withMessages([
                'leave_type_id' => "Insufficient leave balance. You have {$remaining} day(s) remaining for this leave type in {$year}, but you are requesting {$requestedDays} day(s).",
            ]);
        }
    }

    public function rowKey(int $employeeId, int $leaveTypeId, int $year): string
    {
        return $employeeId . '|' . $leaveTypeId . '|' . $year;
    }

    private function loadQuotaContext(
        int $employeeId,
        array $leaveTypeIds,
        int $year,
        bool $withPending = false
    ): array {
        $leaveTypeIds = array_values(array_unique(array_filter($leaveTypeIds)));

        if ($leaveTypeIds === []) {
            return ['quotas' => [], 'adjustments' => [], 'pending' => []];
        }

        $quotas = EmployeeLeaveQuota::query()
            ->where('employee_id', $employeeId)
            ->where('year', $year)
            ->whereIn('leave_type_id', $leaveTypeIds)
            ->get()
            ->keyBy('leave_type_id');

        $adjustments = $this->loadAdjustmentTotals([$employeeId], $leaveTypeIds, [$year]);

        $pending = [];

        if ($withPending) {
            $pending = EmployeLeaveRequest::query()
                ->selectRaw('leave_type_id, SUM(duration) as total_pending')
                ->where('from_employee_id', $employeeId)
                ->whereYear('start_date', $year)
                ->whereIn('leave_type_id', $leaveTypeIds)
                ->whereIn('status', [0, 1])
                ->whereIn('action_type', self::MASTER_ACTION_TYPES)
                ->groupBy('leave_type_id')
                ->pluck('total_pending', 'leave_type_id')
                ->map(fn ($value) => (float) $value)
                ->all();
        }

        $adjustmentsByLeaveType = [];

        foreach ($leaveTypeIds as $leaveTypeId) {
            $key = $this->rowKey($employeeId, (int) $leaveTypeId, $year);
            $adjustmentsByLeaveType[(int) $leaveTypeId] = $adjustments[$key] ?? 0.0;
        }

        return [
            'quotas' => $quotas->all(),
            'adjustments' => $adjustmentsByLeaveType,
            'pending' => $pending,
        ];
    }

    private function loadAdjustmentTotals(array $employeeIds, array $leaveTypeIds, array $years): array
    {
        if ($employeeIds === [] || $leaveTypeIds === [] || $years === []) {
            return [];
        }

        $rows = LeaveBalanceAdjustment::query()
            ->selectRaw('employee_id, leave_type_id, YEAR(created_at) as quota_year')
            ->selectRaw("SUM(CASE WHEN adjustment_type = 'add' THEN days ELSE -days END) as adjustment_total")
            ->whereIn('employee_id', $employeeIds)
            ->whereIn('leave_type_id', $leaveTypeIds)
            ->whereIn(DB::raw('YEAR(created_at)'), $years)
            ->groupBy('employee_id', 'leave_type_id', 'quota_year')
            ->get();

        $totals = [];

        foreach ($rows as $row) {
            $key = $this->rowKey((int) $row->employee_id, (int) $row->leave_type_id, (int) $row->quota_year);
            $totals[$key] = (float) $row->adjustment_total;
        }

        return $totals;
    }

    private function maxAllowedDays(array $context, int $leaveTypeId, float $annualQuotaFallback): float
    {
        $quota = $context['quotas'][$leaveTypeId] ?? null;

        if ($quota === null) {
            return $annualQuotaFallback;
        }

        return (float) $quota->quota + (float) ($context['adjustments'][$leaveTypeId] ?? 0);
    }

    private function usedDays(array $context, int $leaveTypeId): float
    {
        $quota = $context['quotas'][$leaveTypeId] ?? null;

        return $quota ? (float) $quota->used : 0.0;
    }
}
