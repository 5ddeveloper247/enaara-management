<?php

namespace App\Services;

use App\Models\ShiftRosterEntry;
use Carbon\Carbon;

class ShiftRosterFinalizeService
{
    public function __construct(
        private readonly CompensatoryLeaveAwardService $compensatoryLeaveAwardService,
    ) {}

    /**
     * @return array{processed:int, cpl_awarded:int, used:int, skipped:int, errors:array<int, string>}
     */
    public function finalizeForDate(Carbon|string $date): array
    {
        $dateStr = $date instanceof Carbon
            ? $date->toDateString()
            : Carbon::parse($date)->toDateString();

        $entries = ShiftRosterEntry::query()
            ->whereDate('roster_date', $dateStr)
            ->where('status', 'pending')
            ->whereNotNull('employee_id')
            ->where(function ($query) {
                $query->whereHas('approvalRequest', fn ($approvalQuery) => $approvalQuery->where('approval_status', 'approved'))
                    ->orWhereHas('approvalSegment', fn ($segmentQuery) => $segmentQuery->where('approval_status', 'approved'));
            })
            ->with(['employee.department', 'employee.organization'])
            ->orderBy('id')
            ->get();

        $stats = [
            'processed' => 0,
            'cpl_awarded' => 0,
            'used' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        foreach ($entries as $entry) {
            try {
                $this->finalizeEntry($entry, $stats);
            } catch (\Throwable $exception) {
                $stats['errors'][$entry->id] = $exception->getMessage();
            }
        }

        return $stats;
    }

    private function finalizeEntry(ShiftRosterEntry $entry, array &$stats): void
    {
        $stats['processed']++;

        if (! $this->compensatoryLeaveAwardService->isWorkShift($entry)) {
            $entry->status = $this->compensatoryLeaveAwardService->finalizedStatusForEntry($entry);
            $entry->save();
            $stats['skipped']++;

            return;
        }

        if ($this->compensatoryLeaveAwardService->awardForRosterEntry($entry)) {
            $stats['cpl_awarded']++;

            return;
        }

        $entry->status = 'used';
        $entry->save();
        $stats['used']++;
    }

    public function pendingCountForDate(Carbon|string $date): int
    {
        $dateStr = $date instanceof Carbon
            ? $date->toDateString()
            : Carbon::parse($date)->toDateString();

        return ShiftRosterEntry::query()
            ->whereDate('roster_date', $dateStr)
            ->where('status', 'pending')
            ->whereNotNull('employee_id')
            ->where(function ($query) {
                $query->whereHas('approvalRequest', fn ($approvalQuery) => $approvalQuery->where('approval_status', 'approved'))
                    ->orWhereHas('approvalSegment', fn ($segmentQuery) => $segmentQuery->where('approval_status', 'approved'));
            })
            ->count();
    }
}
