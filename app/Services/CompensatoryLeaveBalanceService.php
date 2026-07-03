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
        return (float) count($this->earnedSlotsValidOn($employeeId, $asOf ?? Carbon::today()));
    }

    public function reservedDays(int $employeeId, int $leaveTypeId, ?int $year = null, ?Carbon $asOf = null): float
    {
        $asOf = ($asOf ?? Carbon::today())->copy()->startOfDay();
        $year = $year ?? (int) $asOf->year;

        $validEarned = $this->validEarnedDays($employeeId, $asOf);
        $remaining = $this->remainingDays($employeeId, $year, $asOf);

        return max(0.0, $validEarned - $remaining);
    }

    public function remainingDays(int $employeeId, ?int $year = null, ?Carbon $asOf = null): float
    {
        $cplType = $this->compensatoryLeaveAwardService->resolveCompensatoryLeaveType();

        if ($cplType === null) {
            return 0.0;
        }

        $asOf = ($asOf ?? Carbon::today())->copy()->startOfDay();
        $year = $year ?? (int) $asOf->year;

        $snapshot = $this->buildFifoSnapshot($employeeId, (int) $cplType->id, $year, $asOf);

        return (float) count(array_filter(
            $snapshot['slots'],
            fn (array $slot) => ! $slot['consumed'] && $this->slotIsValidOn($slot, $asOf)
        ));
    }

    public function isCompensatoryLeaveTypeId(int $leaveTypeId): bool
    {
        $cplType = $this->compensatoryLeaveAwardService->resolveCompensatoryLeaveType();

        return $cplType !== null && (int) $cplType->id === $leaveTypeId;
    }

    /**
     * @return array{
     *     slots: array<int, array{earned_date: Carbon, expiry_date: Carbon, consumed: bool, consumed_for: ?string}>,
     *     usage_events: array<int, array{leave_date: Carbon, duration: float, source: string}>
     * }
     */
    public function buildFifoSnapshot(
        int $employeeId,
        int $leaveTypeId,
        ?int $year = null,
        ?Carbon $asOf = null,
        ?int $excludeRequestId = null,
    ): array {
        $asOf = ($asOf ?? Carbon::today())->copy()->startOfDay();
        $year = $year ?? (int) $asOf->year;

        $slots = $this->loadEarnedSlots($employeeId);
        $usageEvents = $this->collectUsageEvents($employeeId, $leaveTypeId, $year, $excludeRequestId);

        $this->applyFifoConsumption($slots, $usageEvents);

        return [
            'slots' => $slots,
            'usage_events' => $usageEvents,
        ];
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
                .'You have '.$availableOnLeaveDate.' day(s) available after pending or approved usage (earliest earned is used first), '
                ."but you are requesting {$requestedDays} day(s).";
        }

        return "Insufficient compensatory leave balance for leave starting {$leaveDateLabel}. "
            .'You have '.$availableOnLeaveDate.' day(s) available (earned days expire after '
            .self::EXPIRY_DAYS." days), but you are requesting {$requestedDays} day(s).";
    }

    /**
     * @return array<int, array{earned_date: Carbon, expiry_date: Carbon, consumed: bool, consumed_for: ?string}>
     */
    private function loadEarnedSlots(int $employeeId): array
    {
        return ShiftRosterEntry::query()
            ->where('employee_id', $employeeId)
            ->where('is_compensatory_earned', true)
            ->orderBy('roster_date')
            ->orderBy('id')
            ->get(['id', 'roster_date'])
            ->map(function ($entry) {
                $earnedDate = Carbon::parse($entry->roster_date)->startOfDay();

                return [
                    'earned_date' => $earnedDate,
                    'expiry_date' => $earnedDate->copy()->addDays(self::EXPIRY_DAYS),
                    'consumed' => false,
                    'consumed_for' => null,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{earned_date: Carbon, expiry_date: Carbon, consumed: bool, consumed_for: ?string}>
     */
    private function earnedSlotsValidOn(int $employeeId, Carbon $asOf): array
    {
        return array_values(array_filter(
            $this->loadEarnedSlots($employeeId),
            fn (array $slot) => $this->slotIsValidOn($slot, $asOf)
        ));
    }

    /**
     * @param  array{earned_date: Carbon, expiry_date: Carbon, consumed: bool, consumed_for: ?string}  $slot
     */
    private function slotIsValidOn(array $slot, Carbon $asOf): bool
    {
        $asOf = $asOf->copy()->startOfDay();

        return $slot['earned_date']->lte($asOf) && $slot['expiry_date']->gte($asOf);
    }

    /**
     * @return array<int, array{leave_date: Carbon, duration: float, source: string}>
     */
    private function collectUsageEvents(
        int $employeeId,
        int $leaveTypeId,
        int $year,
        ?int $excludeRequestId = null,
    ): array {
        $events = [];
        $excludeBlock = $this->resolveExcludeBlock($excludeRequestId);

        $entities = EmployeLeaveEntity::query()
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->whereYear('leave_date', $year)
            ->whereIn('status', [0, 1])
            ->orderBy('leave_date')
            ->get(['leave_date', 'duration']);

        foreach ($entities as $entity) {
            $events[] = [
                'leave_date' => Carbon::parse($entity->leave_date)->startOfDay(),
                'duration' => max(0.0, (float) $entity->duration),
                'source' => 'entity',
            ];
        }

        $pendingBlocks = $this->getDedupedRequestBlocks(
            $employeeId,
            $leaveTypeId,
            $year,
            [0, 1],
            $excludeBlock
        );

        foreach ($pendingBlocks as $block) {
            $duration = max(0.0, (float) $block['duration']);

            if ($duration <= 0) {
                continue;
            }

            $events[] = [
                'leave_date' => Carbon::parse($block['start_date'])->startOfDay(),
                'duration' => $duration,
                'source' => 'pending_request',
            ];
        }

        if ($entities->isEmpty()) {
            $approvedBlocks = $this->getDedupedRequestBlocks(
                $employeeId,
                $leaveTypeId,
                $year,
                [3],
                $excludeBlock
            );

            foreach ($approvedBlocks as $block) {
                $duration = max(0.0, (float) $block['duration']);

                if ($duration <= 0) {
                    continue;
                }

                $events[] = [
                    'leave_date' => Carbon::parse($block['start_date'])->startOfDay(),
                    'duration' => $duration,
                    'source' => 'approved_request',
                ];
            }
        }

        return $this->sortUsageEvents($events);
    }

    /**
     * Consume earliest eligible earned slot for each leave usage event (FIFO).
     *
     * @param  array<int, array{earned_date: Carbon, expiry_date: Carbon, consumed: bool, consumed_for: ?string}>  $slots
     * @param  array<int, array{leave_date: Carbon, duration: float, source: string}>  $usageEvents
     */
    private function applyFifoConsumption(array &$slots, array $usageEvents): void
    {
        foreach ($usageEvents as $event) {
            $units = $this->durationUnits($event['duration']);
            $leaveDate = $event['leave_date']->copy()->startOfDay();

            for ($i = 0; $i < $units; $i++) {
                $slotIndex = $this->findNextConsumableSlotIndex($slots, $leaveDate);

                if ($slotIndex === null) {
                    break;
                }

                $slots[$slotIndex]['consumed'] = true;
                $slots[$slotIndex]['consumed_for'] = $leaveDate->toDateString();
            }
        }
    }

    /**
     * @param  array<int, array{earned_date: Carbon, expiry_date: Carbon, consumed: bool, consumed_for: ?string}>  $slots
     */
    private function findNextConsumableSlotIndex(array $slots, Carbon $leaveDate): ?int
    {
        foreach ($slots as $index => $slot) {
            if ($slot['consumed']) {
                continue;
            }

            if ($slot['earned_date']->gt($leaveDate)) {
                continue;
            }

            if ($slot['expiry_date']->lt($leaveDate)) {
                continue;
            }

            return $index;
        }

        return null;
    }

    private function durationUnits(float $duration): int
    {
        if ($duration <= 0) {
            return 0;
        }

        if ($duration <= 0.5) {
            return 1;
        }

        return (int) ceil($duration);
    }

    /**
     * @return array<int, array{leave_date: Carbon, duration: float, source: string}>
     */
    private function sortUsageEvents(array $events): array
    {
        usort($events, function (array $a, array $b) {
            $dateCompare = $a['leave_date']->timestamp <=> $b['leave_date']->timestamp;

            return $dateCompare !== 0 ? $dateCompare : ($a['source'] <=> $b['source']);
        });

        return $events;
    }

    /**
     * @return array<int, array{start_date: string, end_date: string, duration: float}>
     */
    private function getDedupedRequestBlocks(
        int $employeeId,
        int $leaveTypeId,
        int $year,
        array $statuses,
        ?array $excludeBlock = null,
    ): array {
        $base = EmployeLeaveRequest::query()
            ->where('from_employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->whereYear('start_date', $year)
            ->whereIn('action_type', self::REQUEST_ACTION_TYPES_FOR_BALANCE)
            ->whereIn('status', $statuses);

        if ($excludeBlock !== null) {
            $base->whereNot(function ($query) use ($excludeBlock) {
                $query->whereDate('start_date', $excludeBlock['start_date'])
                    ->whereDate('end_date', $excludeBlock['end_date']);
            });
        }

        return DB::query()
            ->fromSub(
                (clone $base)
                    ->selectRaw('start_date, end_date, MAX(GREATEST(duration - COALESCE(exempt_days, 0), 0)) as duration')
                    ->groupBy('start_date', 'end_date'),
                'deduped_blocks'
            )
            ->orderBy('start_date')
            ->get()
            ->map(fn ($row) => [
                'start_date' => (string) $row->start_date,
                'end_date' => (string) $row->end_date,
                'duration' => (float) $row->duration,
            ])
            ->all();
    }

    /**
     * @return array{start_date: string, end_date: string}|null
     */
    private function resolveExcludeBlock(?int $excludeRequestId): ?array
    {
        if ($excludeRequestId === null) {
            return null;
        }

        $request = EmployeLeaveRequest::query()
            ->whereKey($excludeRequestId)
            ->first(['start_date', 'end_date']);

        if ($request === null) {
            return null;
        }

        return [
            'start_date' => Carbon::parse($request->start_date)->toDateString(),
            'end_date' => Carbon::parse($request->end_date)->toDateString(),
        ];
    }
}
