<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\OutsourcedEmployee;
use App\Models\ShiftPlanner;
use App\Models\ShiftRosterApprovalRequest;
use App\Models\ShiftRosterApprovalRequestItem;
use App\Models\ShiftRosterApprovalSegment;
use App\Models\ShiftRosterEntry;
use App\Models\User;
use App\Notifications\ShiftRosterApprovalRequiredNotification;
use App\Notifications\ShiftRosterApprovedNotification;
use App\Notifications\ShiftRosterRejectedNotification;
use App\Services\leaverequestPrivatefunctions\LeaveRequestNotifier;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShiftRosterApprovalService
{
    public function __construct(
        private readonly ShiftRosterApproverResolver $approverResolver,
        private readonly ShiftRosterService $shiftRosterService,
        private readonly LeaveRequestNotifier $notifier,
    ) {
    }

    public function submitSingle(array $data): ShiftRosterApprovalRequest
    {
        $employeeType = ($data['employee_type'] ?? 'employee') === 'outsourced' ? 'outsourced' : 'employee';
        $employeeId = (int) $data['employee_id'];
        $rosterDate = (string) $data['roster_date'];

        $this->assertNoExistingOffDay($employeeType, $employeeId, $rosterDate);

        $assignment = $this->shiftRosterService->buildEntryAssignmentForApproval($data);
        $entryStatus = (($data['status'] ?? 1) == 1) ? 'pending' : 'cancelled';

        $item = [
            'roster_date' => $rosterDate,
            'entry_type' => 'shift',
            'shift_planner_id' => $assignment['shift_planner_id'],
            'is_custom_time' => $assignment['is_custom_time'],
            'start_time' => $assignment['start_time'],
            'end_time' => $assignment['end_time'],
            'floor' => $this->shiftRosterService->resolveFloorLabelFromData($data),
            'location_text' => $this->normalizeOptionalString($data['location_text'] ?? null),
            'notes' => $this->normalizeOptionalString($data['notes'] ?? null),
            'entry_status' => $entryStatus,
        ];

        return $this->createRequest(
            assigneeType: $employeeType,
            assigneeId: $employeeId,
            requestType: 'single',
            items: [$item],
            shiftLabel: $this->resolveShiftLabel($assignment['shift_planner_id'], $assignment['is_custom_time'])
        );
    }

    public function submitUpdate(int $entryId, array $data): ShiftRosterApprovalRequest
    {
        $entry = ShiftRosterEntry::query()->findOrFail($entryId);
        [$assigneeType, $assigneeId] = $this->resolveAssigneeFromEntry($entry);

        $rosterDate = (string) ($data['roster_date'] ?? $entry->roster_date?->toDateString());
        $this->assertNoPendingApprovalForAssigneeDate($assigneeType, $assigneeId, $rosterDate);

        $item = $this->shiftRosterService->buildUpdateApprovalItem($entry, $data);
        $baseLabel = $this->shiftRosterService->resolveApprovalLabelForEntry($entry);

        return $this->createRequest(
            assigneeType: $assigneeType,
            assigneeId: $assigneeId,
            requestType: 'update',
            items: [$item],
            shiftLabel: 'Update: ' . $baseLabel
        );
    }

    public function submitDelete(int $entryId): ShiftRosterApprovalRequest
    {
        $entry = ShiftRosterEntry::query()->findOrFail($entryId);
        [$assigneeType, $assigneeId] = $this->resolveAssigneeFromEntry($entry);

        $rosterDate = $entry->roster_date?->toDateString();
        if (! $rosterDate) {
            throw ValidationException::withMessages([
                'roster_date' => 'Unable to submit roster removal for approval.',
            ]);
        }

        $this->assertNoPendingApprovalForAssigneeDate($assigneeType, $assigneeId, $rosterDate);

        $item = $this->shiftRosterService->buildDeleteApprovalItem($entry);
        $baseLabel = $this->shiftRosterService->resolveApprovalLabelForEntry($entry);

        return $this->createRequest(
            assigneeType: $assigneeType,
            assigneeId: $assigneeId,
            requestType: 'delete',
            items: [$item],
            shiftLabel: 'Remove: ' . $baseLabel
        );
    }

    /**
     * Submit all draft roster entries in a month as one roster-level GM approval request.
     */
    public function submitPendingEntriesForApproval(int $year, int $month, string $employeeGroup = 'internal'): ShiftRosterApprovalRequest
    {
        $periodStart = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $periodEnd = $periodStart->copy()->endOfMonth();

        $entriesQuery = ShiftRosterEntry::query()
            ->whereNull('shift_roster_approval_request_id')
            ->whereBetween('roster_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->whereIn('status', ['pending', 'off'])
            ->orderBy('roster_date')
            ->orderBy('id');

        if ($employeeGroup === 'third_party') {
            $entriesQuery->whereNotNull('outsourced_employee_id');
        } else {
            $entriesQuery->whereNotNull('employee_id');
        }

        $entries = $entriesQuery
            ->get()
            ->filter(fn (ShiftRosterEntry $entry) => $this->shiftRosterService->isDraftEntryOwnedByUser(
                $entry,
                Auth::id()
            ))
            ->values();

        if ($entries->isEmpty()) {
            throw ValidationException::withMessages([
                'approval' => 'No draft roster entries found to submit for approval.',
            ]);
        }

        $shiftLabel = $periodStart->format('F Y') . ' Roster';
        if ($employeeGroup === 'third_party') {
            $shiftLabel .= ' (Third-party)';
        }

        $existingRequest = $this->findPendingRosterRequestForPeriod($periodStart, $employeeGroup);
        if ($existingRequest) {
            return $this->appendDraftsToPendingRosterRequest($existingRequest, $entries);
        }

        $items = $entries
            ->map(fn (ShiftRosterEntry $entry) => $this->buildItemFromEntry($entry))
            ->values()
            ->all();

        return $this->createRosterRequest($items, $shiftLabel, $entries);
    }

    public function submitBulkForAssignee(string $assigneeType, int $assigneeId, array $items, ?string $shiftLabel = null): ?ShiftRosterApprovalRequest
    {
        if ($items === []) {
            return null;
        }

        $dates = collect($items)->pluck('roster_date')->filter()->sort()->values();
        if ($dates->isEmpty()) {
            return null;
        }

        return $this->createRequest(
            assigneeType: $assigneeType,
            assigneeId: $assigneeId,
            requestType: 'bulk',
            items: $items,
            shiftLabel: $shiftLabel
        );
    }

    public function approve(int $requestId, ?int $userId = null): ShiftRosterApprovalRequest
    {
        return DB::transaction(function () use ($requestId, $userId) {
            $request = ShiftRosterApprovalRequest::query()
                ->with(['items', 'employee', 'outsourcedEmployee', 'segments'])
                ->findOrFail($requestId);

            if (! $request->isPending()) {
                throw ValidationException::withMessages([
                    'approval' => 'This roster request has already been processed.',
                ]);
            }

            if ($request->request_type === 'roster') {
                return $this->approveRosterSegment($request, $userId ?? Auth::id());
            }

            $this->assertUserCanApprove($request, $userId ?? Auth::id());

            $linkedEntryCount = ShiftRosterEntry::query()
                ->where('shift_roster_approval_request_id', $request->id)
                ->count();

            if ($linkedEntryCount === 0) {
                foreach ($request->items as $item) {
                    $this->applyApprovedItem($request, $item, $userId ?? Auth::id());
                }
            }

            $request->update([
                'approval_status' => 'approved',
                'approved_by' => $userId ?? Auth::id(),
                'approved_at' => now(),
            ]);

            $this->shiftRosterService->clearPublishedSnapshotsForEntries(
                ShiftRosterEntry::query()
                    ->where('shift_roster_approval_request_id', $request->id)
                    ->pluck('id')
            );

            $request->refresh();
            $this->notifyRequesterApproved($request);

            if ($request->employee_id) {
                $this->shiftRosterService->syncCompensatoryTagsForEmployeeInRange(
                    (int) $request->employee_id,
                    $request->start_date->toDateString(),
                    $request->end_date->toDateString()
                );
            }

            return $request->load(['items.shift', 'employee.department', 'outsourcedEmployee', 'requestedByUser', 'approverEmployee.role']);
        });
    }

    public function reject(int $requestId, ?string $reason = null, ?int $userId = null): ShiftRosterApprovalRequest
    {
        return DB::transaction(function () use ($requestId, $reason, $userId) {
            $request = ShiftRosterApprovalRequest::query()->findOrFail($requestId);

            if (! $request->isPending()) {
                throw ValidationException::withMessages([
                    'approval' => 'This roster request has already been processed.',
                ]);
            }

            if ($request->request_type === 'roster') {
                return $this->rejectRosterSegment($request, $reason, $userId ?? Auth::id());
            }

            $this->assertUserCanApprove($request, $userId ?? Auth::id());

            $linkedEntries = ShiftRosterEntry::query()
                ->where('shift_roster_approval_request_id', $request->id)
                ->get();

            foreach ($linkedEntries as $entry) {
                if (is_array($entry->published_snapshot) && $entry->published_snapshot !== []) {
                    $this->shiftRosterService->restorePublishedSnapshot($entry);

                    continue;
                }

                $entry->update([
                    'shift_roster_approval_request_id' => null,
                    'shift_roster_approval_segment_id' => null,
                ]);
            }

            $request->update([
                'approval_status' => 'rejected',
                'rejected_by' => $userId ?? Auth::id(),
                'rejected_at' => now(),
                'rejection_reason' => $reason,
            ]);

            $request->refresh();
            $this->notifyRequesterRejected($request);

            return $request->fresh();
        });
    }

    /**
     * Rebuild a pending roster approval request from its linked entries after the requester edits a shift.
     */
    public function syncPendingRosterRequest(int $requestId, ?int $changedSegmentId = null): void
    {
        DB::transaction(function () use ($requestId, $changedSegmentId) {
            $request = ShiftRosterApprovalRequest::query()
                ->with(['segments.department', 'segments.approverEmployee'])
                ->find($requestId);

            if (! $request || ! $request->isPending()) {
                return;
            }

            $entries = ShiftRosterEntry::query()
                ->where('shift_roster_approval_request_id', $request->id)
                ->orderBy('roster_date')
                ->orderBy('id')
                ->get();

            $itemPayloads = $entries
                ->map(fn (ShiftRosterEntry $entry) => $this->buildItemFromEntry($entry))
                ->values();

            $request->items()->delete();

            foreach ($itemPayloads as $item) {
                $request->items()->create($item);
            }

            $request->update([
                'shift_count' => $itemPayloads->where('entry_type', 'shift')->count(),
                'off_day_count' => $itemPayloads->where('entry_type', 'off')->count(),
                'start_date' => $entries->min(fn (ShiftRosterEntry $entry) => $entry->roster_date?->toDateString())
                    ?? $request->start_date,
                'end_date' => $entries->max(fn (ShiftRosterEntry $entry) => $entry->roster_date?->toDateString())
                    ?? $request->end_date,
            ]);

            foreach ($request->segments as $segment) {
                $segmentEntries = $entries->where('shift_roster_approval_segment_id', $segment->id);
                $segmentItems = $segmentEntries
                    ->map(fn (ShiftRosterEntry $entry) => $this->buildItemFromEntry($entry))
                    ->values();

                $segment->update([
                    'shift_count' => $segmentItems->where('entry_type', 'shift')->count(),
                    'off_day_count' => $segmentItems->where('entry_type', 'off')->count(),
                    'employee_count' => $segmentEntries
                        ->groupBy(fn (ShiftRosterEntry $entry) => $entry->employee_id
                            ? 'employee:' . $entry->employee_id
                            : 'outsourced:' . $entry->outsourced_employee_id)
                        ->count(),
                ]);
            }

            $segmentToNotify = $changedSegmentId
                ? $request->segments->firstWhere('id', $changedSegmentId)
                : null;

            if ($segmentToNotify?->approverEmployee) {
                $this->notifier->notifyApprover(
                    $segmentToNotify->approverEmployee,
                    new ShiftRosterApprovalRequiredNotification($request, $segmentToNotify)
                );
            } elseif ($request->request_type !== 'roster' && $request->approverEmployee) {
                $this->notifier->notifyApprover(
                    $request->approverEmployee,
                    new ShiftRosterApprovalRequiredNotification($request)
                );
            }
        });
    }

    public function getPendingForApprover(?int $approverEmployeeId): Collection
    {
        if (! $approverEmployeeId) {
            return collect();
        }

        $legacyRequests = ShiftRosterApprovalRequest::query()
            ->with(['employee.department', 'outsourcedEmployee', 'requestedByUser'])
            ->where('approval_status', 'pending')
            ->where('approver_employee_id', $approverEmployeeId)
            ->where('request_type', '!=', 'roster')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (ShiftRosterApprovalRequest $request) => [
                'request' => $request,
                'segment' => null,
            ]);

        $rosterSegments = ShiftRosterApprovalSegment::query()
            ->with(['request.requestedByUser', 'department'])
            ->where('approver_employee_id', $approverEmployeeId)
            ->where('approval_status', 'pending')
            ->whereHas('request', fn ($query) => $query
                ->where('approval_status', 'pending')
                ->where('request_type', 'roster'))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (ShiftRosterApprovalSegment $segment) => [
                'request' => $segment->request,
                'segment' => $segment,
            ]);

        return $legacyRequests->concat($rosterSegments)->values();
    }

    public function formatPendingListItem(ShiftRosterApprovalRequest $request, ?ShiftRosterApprovalSegment $segment = null): array
    {
        $assigneeName = $this->resolveAssigneeDisplayName($request, $segment);

        if ($request->request_type === 'roster') {
            $initials = 'RO';
            $department = $segment
                ? ($segment->department?->name ?? 'Third-party')
                : $this->countAssigneesForRequest($request) . ' employees';
            $shiftCount = $segment ? (int) $segment->shift_count : (int) $request->shift_count;
            $offDayCount = $segment ? (int) $segment->off_day_count : (int) $request->off_day_count;
            $durationLabel = $segment
                ? $this->buildSegmentDurationLabel($request, $segment)
                : $this->buildDurationLabel($request);
        } else {
            $words = preg_split('/\s+/u', trim($assigneeName), 2, PREG_SPLIT_NO_EMPTY) ?: [];
            $initials = strtoupper(substr($words[0] ?? 'E', 0, 1) . substr($words[1] ?? '', 0, 1));
            $department = $request->employee?->department?->name ?? '-';
            $shiftCount = (int) $request->shift_count;
            $offDayCount = (int) $request->off_day_count;
            $durationLabel = $this->buildDurationLabel($request);
        }

        return [
            'id' => $request->id,
            'segment_id' => $segment?->id,
            'name' => $assigneeName,
            'initials' => $initials !== '' ? $initials : 'E',
            'department' => $department,
            'requested_by' => $request->requestedByUser?->name ?? 'Unknown',
            'request_date' => $request->created_at?->format('M d, Y') ?? '-',
            'start_date' => $request->start_date?->format('M d, Y') ?? '-',
            'end_date' => $request->end_date?->format('M d, Y') ?? '-',
            'shift_count' => $shiftCount,
            'off_day_count' => $offDayCount,
            'shift_label' => $request->shift_label ?? 'Shift roster',
            'duration_label' => $durationLabel,
        ];
    }

    public function buildDetailPayload(int $requestId, ?int $viewerEmployeeId = null): array
    {
        $request = ShiftRosterApprovalRequest::query()
            ->with([
                'items.shift',
                'employee.department',
                'employee.role',
                'outsourcedEmployee',
                'requestedByUser.employee.role',
                'approverEmployee.role',
                'segments.department',
                'segments.approverEmployee.role',
            ])
            ->findOrFail($requestId);

        $viewerSegment = $this->resolveViewerSegment($request, $viewerEmployeeId);

        if ($viewerEmployeeId && ! $this->viewerCanAccessRequest($request, $viewerEmployeeId)) {
            $user = Auth::user();
            if (! $user || ! $user->isSystemAdminUser()) {
                throw ValidationException::withMessages([
                    'approval' => 'You are not authorized to view this roster request.',
                ]);
            }
        }

        $assigneeName = $this->resolveAssigneeDisplayName($request, $viewerSegment);

        if ($request->request_type === 'roster') {
            $initials = 'RO';
        } else {
            $words = preg_split('/\s+/u', trim($assigneeName), 2, PREG_SPLIT_NO_EMPTY) ?: [];
            $initials = strtoupper(substr($words[0] ?? 'E', 0, 1) . substr($words[1] ?? '', 0, 1));
        }

        $items = $request->items
            ->sortBy(fn (ShiftRosterApprovalRequestItem $item) => $item->roster_date?->format('Y-m-d'))
            ->values();

        $pendingSegments = $request->segments->where('approval_status', 'pending')->count();
        $approvedSegments = $request->segments->where('approval_status', 'approved')->count();

        return [
            'id' => $request->id,
            'segment_id' => $viewerSegment?->id,
            'approval_status' => $request->approval_status,
            'request_type' => $request->request_type,
            'review_year' => $request->start_date?->year,
            'review_month' => $request->start_date?->month,
            'employee_id' => $request->employee_id,
            'outsourced_employee_id' => $request->outsourced_employee_id,
            'assignee_name' => $assigneeName,
            'assignee_initials' => $initials !== '' ? $initials : 'E',
            'department' => $viewerSegment?->department?->name
                ?? $request->employee?->department?->name
                ?? '-',
            'requested_by' => $request->requestedByUser?->name ?? 'Unknown',
            'requested_by_role' => $request->requestedByUser?->employee?->role?->name ?? '-',
            'approver_name' => $viewerSegment?->approverEmployee?->full_name
                ?? $request->approverEmployee?->full_name
                ?? $request->approverEmployee?->first_name
                ?? '-',
            'approver_role' => $viewerSegment?->approverEmployee?->role?->name
                ?? $request->approverEmployee?->role?->name
                ?? '-',
            'start_date' => $request->start_date?->format('d M'),
            'end_date' => $request->end_date?->format('d M Y'),
            'period_label' => $this->buildPeriodLabel($request),
            'shift_count' => $viewerSegment ? (int) $viewerSegment->shift_count : (int) $request->shift_count,
            'off_day_count' => $viewerSegment ? (int) $viewerSegment->off_day_count : (int) $request->off_day_count,
            'shift_label' => $request->shift_label ?? 'Shift roster',
            'duration_label' => $viewerSegment
                ? $this->buildSegmentDurationSummary($viewerSegment)
                : $this->buildDurationSummary($request),
            'segment_scope_label' => $viewerSegment
                ? $this->buildSegmentScopeLabel($request, $viewerSegment)
                : null,
            'can_approve' => $viewerSegment?->isPending() ?? (
                $request->request_type !== 'roster' && $request->isPending()
            ),
            'pending_segments' => $pendingSegments,
            'approved_segments' => $approvedSegments,
            'first_review_week' => $this->resolveFirstReviewWeek($request, $viewerSegment),
            'employee_group' => $this->resolveReviewEmployeeGroup($request, $viewerSegment),
            'total_items' => $items->count(),
            'items' => $items
                ->map(fn (ShiftRosterApprovalRequestItem $item) => [
                    'date' => $item->roster_date?->format('D d M Y'),
                    'entry_type' => $item->entry_type,
                    'shift_name' => $item->entry_type === 'delete'
                        ? 'Remove shift'
                        : ($item->entry_type === 'off'
                            ? 'Off'
                            : ($item->shift?->name ?? ($item->is_custom_time ? 'Custom' : 'Shift'))),
                    'start_time' => $this->formatTimeForDisplay($item->start_time),
                    'end_time' => $this->formatTimeForDisplay($item->end_time),
                    'floor' => $item->floor ?? '-',
                    'location' => $item->location_text ?? '-',
                    'notes' => $item->notes ?? '-',
                    'status' => $item->entry_status,
                ])
                ->all(),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    private function createRequest(
        string $assigneeType,
        int $assigneeId,
        string $requestType,
        array $items,
        ?string $shiftLabel = null
    ): ShiftRosterApprovalRequest {
        return DB::transaction(function () use ($assigneeType, $assigneeId, $requestType, $items, $shiftLabel) {
            $gm = $this->approverResolver->resolveGmForAssignee($assigneeType, $assigneeId);

            if ($gm === null) {
                throw ValidationException::withMessages([
                    'employee_id' => 'No GM was found for this employee. Roster cannot be submitted for approval.',
                ]);
            }

            $sortedDates = collect($items)->pluck('roster_date')->sort()->values();
            $shiftCount = collect($items)->where('entry_type', 'shift')->count();
            $offDayCount = collect($items)->where('entry_type', 'off')->count();

            $request = ShiftRosterApprovalRequest::query()->create([
                'request_type' => $requestType,
                'employee_id' => $assigneeType === 'employee' ? $assigneeId : null,
                'outsourced_employee_id' => $assigneeType === 'outsourced' ? $assigneeId : null,
                'approver_employee_id' => $gm->id,
                'requested_by' => Auth::id(),
                'start_date' => $sortedDates->first(),
                'end_date' => $sortedDates->last(),
                'shift_count' => $shiftCount,
                'off_day_count' => $offDayCount,
                'shift_label' => $shiftLabel ?? $this->resolveShiftLabelFromItems($items),
                'approval_status' => 'pending',
            ]);

            foreach ($items as $item) {
                $request->items()->create($item);
            }

            $request->load(['employee.department', 'outsourcedEmployee', 'requestedByUser']);
            $this->notifier->notifyApprover($gm, new ShiftRosterApprovalRequiredNotification($request));

            return $request;
        });
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @param Collection<int, ShiftRosterEntry> $entries
     */
    private function createRosterRequest(array $items, string $shiftLabel, Collection $entries): ShiftRosterApprovalRequest
    {
        return DB::transaction(function () use ($items, $shiftLabel, $entries) {
            $entries->load(['employee.department', 'outsourcedEmployee']);

            $sortedDates = collect($items)->pluck('roster_date')->sort()->values();
            $shiftCount = collect($items)->where('entry_type', 'shift')->count();
            $offDayCount = collect($items)->where('entry_type', 'off')->count();

            $request = ShiftRosterApprovalRequest::query()->create([
                'request_type' => 'roster',
                'employee_id' => null,
                'outsourced_employee_id' => null,
                'approver_employee_id' => null,
                'requested_by' => Auth::id(),
                'start_date' => $sortedDates->first(),
                'end_date' => $sortedDates->last(),
                'shift_count' => $shiftCount,
                'off_day_count' => $offDayCount,
                'shift_label' => $shiftLabel,
                'approval_status' => 'pending',
            ]);

            foreach ($items as $item) {
                $request->items()->create($item);
            }

            $departmentGroups = $entries->groupBy(function (ShiftRosterEntry $entry) {
                if ($entry->employee_id) {
                    return 'department:' . (int) ($entry->employee?->department_id ?? 0);
                }

                return 'outsourced:' . (int) ($entry->outsourcedEmployee?->sbu_id ?? 0);
            });

            $notifiedApprovers = [];

            foreach ($departmentGroups as $groupEntries) {
                $firstEntry = $groupEntries->first();
                $departmentId = $firstEntry->employee_id
                    ? ($firstEntry->employee?->department_id ? (int) $firstEntry->employee->department_id : null)
                    : null;

                $gm = $firstEntry->employee_id
                    ? $this->approverResolver->resolveGmForEmployee($firstEntry->employee)
                    : $this->approverResolver->resolveGmForOutsourcedEmployee($firstEntry->outsourcedEmployee);

                if ($gm === null) {
                    $departmentName = $firstEntry->employee?->department?->name ?? 'Third-party';
                    throw ValidationException::withMessages([
                        'approval' => 'No department head was found for ' . $departmentName . '. Roster cannot be submitted.',
                    ]);
                }

                $segmentItems = $groupEntries
                    ->map(fn (ShiftRosterEntry $entry) => $this->buildItemFromEntry($entry));

                $segment = $request->segments()->create([
                    'department_id' => $departmentId,
                    'approver_employee_id' => $gm->id,
                    'shift_count' => $segmentItems->where('entry_type', 'shift')->count(),
                    'off_day_count' => $segmentItems->where('entry_type', 'off')->count(),
                    'employee_count' => $groupEntries
                        ->groupBy(fn (ShiftRosterEntry $entry) => $entry->employee_id
                            ? 'employee:' . $entry->employee_id
                            : 'outsourced:' . $entry->outsourced_employee_id)
                        ->count(),
                    'approval_status' => 'pending',
                ]);

                ShiftRosterEntry::query()
                    ->whereIn('id', $groupEntries->pluck('id')->all())
                    ->update([
                        'shift_roster_approval_request_id' => $request->id,
                        'shift_roster_approval_segment_id' => $segment->id,
                    ]);

                if (! in_array($gm->id, $notifiedApprovers, true)) {
                    $segment->load('department');
                    $this->notifier->notifyApprover(
                        $gm,
                        new ShiftRosterApprovalRequiredNotification($request, $segment)
                    );
                    $notifiedApprovers[] = $gm->id;
                }
            }

            return $request->load(['segments.department', 'requestedByUser']);
        });
    }

    private function approveRosterSegment(ShiftRosterApprovalRequest $request, ?int $userId): ShiftRosterApprovalRequest
    {
        $segment = $this->resolveActingSegment($request, $userId);

        if (! $segment->isPending()) {
            throw ValidationException::withMessages([
                'approval' => 'Your department roster segment has already been processed.',
            ]);
        }

        $segment->update([
            'approval_status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        $approvedEntryIds = ShiftRosterEntry::query()
            ->where('shift_roster_approval_segment_id', $segment->id)
            ->pluck('id');
        $this->shiftRosterService->clearPublishedSnapshotsForEntries($approvedEntryIds);

        $employeeIds = ShiftRosterEntry::query()
            ->where('shift_roster_approval_segment_id', $segment->id)
            ->whereNotNull('employee_id')
            ->distinct()
            ->pluck('employee_id');

        foreach ($employeeIds as $employeeId) {
            $this->shiftRosterService->syncCompensatoryTagsForEmployeeInRange(
                (int) $employeeId,
                $request->start_date->toDateString(),
                $request->end_date->toDateString()
            );
        }

        $pendingSegments = $request->segments()->where('approval_status', 'pending')->count();

        if ($pendingSegments === 0) {
            $request->update([
                'approval_status' => 'approved',
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);
            $this->notifyRequesterApproved($request);
        }

        return $request->load(['items.shift', 'segments.department', 'requestedByUser', 'approverEmployee.role']);
    }

    private function rejectRosterSegment(
        ShiftRosterApprovalRequest $request,
        ?string $reason,
        ?int $userId
    ): ShiftRosterApprovalRequest {
        $segment = $this->resolveActingSegment($request, $userId);

        if (! $segment->isPending()) {
            throw ValidationException::withMessages([
                'approval' => 'Your department roster segment has already been processed.',
            ]);
        }

        $segmentEntries = ShiftRosterEntry::query()
            ->where('shift_roster_approval_segment_id', $segment->id)
            ->get();

        foreach ($segmentEntries as $entry) {
            if (is_array($entry->published_snapshot) && $entry->published_snapshot !== []) {
                $this->shiftRosterService->restorePublishedSnapshot($entry);

                continue;
            }

            $entry->update([
                'shift_roster_approval_request_id' => null,
                'shift_roster_approval_segment_id' => null,
            ]);
        }

        $segment->update([
            'approval_status' => 'rejected',
            'rejected_by' => $userId,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        $pendingSegments = $request->segments()->where('approval_status', 'pending')->count();
        $approvedSegments = $request->segments()->where('approval_status', 'approved')->count();

        if ($pendingSegments === 0 && $approvedSegments === 0) {
            $request->update([
                'approval_status' => 'rejected',
                'rejected_by' => $userId,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
            ]);
            $this->notifyRequesterRejected($request);
        }

        return $request->fresh(['segments.department']);
    }

    private function applyApprovedItem(
        ShiftRosterApprovalRequest $request,
        ShiftRosterApprovalRequestItem $item,
        ?int $userId
    ): void {
        if ($request->employee_id) {
            $lookup = [
                'employee_id' => (int) $request->employee_id,
                'roster_date' => $item->roster_date->toDateString(),
            ];
            $payload = ['outsourced_employee_id' => null];
        } else {
            $lookup = [
                'outsourced_employee_id' => (int) $request->outsourced_employee_id,
                'roster_date' => $item->roster_date->toDateString(),
            ];
            $payload = ['employee_id' => null];
        }

        if ($item->entry_type === 'delete') {
            $this->shiftRosterService->deleteApprovedRosterEntry($lookup, $userId);

            return;
        }

        if ($item->entry_type === 'off') {
            $payload += [
                'shift_planner_id' => $item->shift_planner_id,
                'is_custom_time' => false,
                'start_time' => null,
                'end_time' => null,
                'floor' => null,
                'location_text' => null,
                'notes' => null,
                'status' => 'off',
            ];
        } else {
            $payload += [
                'shift_planner_id' => $item->shift_planner_id,
                'is_custom_time' => (bool) $item->is_custom_time,
                'start_time' => $item->start_time,
                'end_time' => $item->end_time,
                'floor' => $item->floor,
                'location_text' => $item->location_text,
                'notes' => $item->notes,
                'status' => $item->entry_status ?: 'pending',
            ];
        }

        $this->shiftRosterService->applyApprovedRosterEntry($lookup, $payload, $userId);
    }

    private function notifyRequesterApproved(ShiftRosterApprovalRequest $request): void
    {
        $requester = User::query()->find($request->requested_by);
        if (! $requester) {
            return;
        }

        $approverName = Auth::user()?->name
            ?? $request->approverEmployee?->full_name
            ?? 'GM';

        $requester->notify(new ShiftRosterApprovedNotification($request, $approverName));
    }

    private function notifyRequesterRejected(ShiftRosterApprovalRequest $request): void
    {
        $requester = User::query()->find($request->requested_by);
        if (! $requester) {
            return;
        }

        $approverName = Auth::user()?->name
            ?? $request->approverEmployee?->full_name
            ?? 'GM';

        $requester->notify(new ShiftRosterRejectedNotification($request, $approverName));
    }

    private function assertUserCanApprove(ShiftRosterApprovalRequest $request, ?int $userId): void
    {
        $user = $userId ? User::query()->find($userId) : Auth::user();

        if (! $user) {
            throw ValidationException::withMessages(['approval' => 'Unauthorized action.']);
        }

        if ($user->isSystemAdminUser()) {
            return;
        }

        if ($request->request_type === 'roster') {
            $this->resolveActingSegment($request, $userId);

            return;
        }

        if ((int) $user->employee_id !== (int) $request->approver_employee_id) {
            throw ValidationException::withMessages([
                'approval' => 'You are not authorized to approve this roster request.',
            ]);
        }
    }

    private function resolveActingSegment(ShiftRosterApprovalRequest $request, ?int $userId): ShiftRosterApprovalSegment
    {
        $user = $userId ? User::query()->find($userId) : Auth::user();

        if (! $user) {
            throw ValidationException::withMessages(['approval' => 'Unauthorized action.']);
        }

        if ($user->isSystemAdminUser()) {
            $segment = $request->segments()->where('approval_status', 'pending')->orderBy('id')->first();
            if ($segment) {
                return $segment;
            }

            throw ValidationException::withMessages([
                'approval' => 'No pending department segment found for this roster request.',
            ]);
        }

        if (! $user->employee_id) {
            throw ValidationException::withMessages([
                'approval' => 'You are not authorized to approve this roster request.',
            ]);
        }

        $segment = $request->segments()
            ->where('approver_employee_id', (int) $user->employee_id)
            ->where('approval_status', 'pending')
            ->orderBy('id')
            ->first();

        if (! $segment) {
            throw ValidationException::withMessages([
                'approval' => 'You are not authorized to approve this roster request.',
            ]);
        }

        return $segment;
    }

    private function resolveViewerSegment(
        ShiftRosterApprovalRequest $request,
        ?int $viewerEmployeeId
    ): ?ShiftRosterApprovalSegment {
        if ($request->request_type !== 'roster' || ! $viewerEmployeeId) {
            return null;
        }

        return $request->segments
            ->firstWhere('approver_employee_id', $viewerEmployeeId);
    }

    private function viewerCanAccessRequest(ShiftRosterApprovalRequest $request, int $viewerEmployeeId): bool
    {
        if ($request->request_type === 'roster') {
            return $request->segments->contains(
                fn (ShiftRosterApprovalSegment $segment) => (int) $segment->approver_employee_id === $viewerEmployeeId
            );
        }

        return (int) $request->approver_employee_id === $viewerEmployeeId;
    }

    private function assertNoPendingApprovalForAssigneeDate(string $assigneeType, int $assigneeId, string $rosterDate): void
    {
        $query = ShiftRosterApprovalRequest::query()
            ->where('approval_status', 'pending')
            ->whereHas('items', fn ($itemQuery) => $itemQuery->whereDate('roster_date', $rosterDate));

        if ($assigneeType === 'outsourced') {
            $query->where('outsourced_employee_id', $assigneeId);
        } else {
            $query->where('employee_id', $assigneeId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'roster_date' => 'A roster change for this date is already pending GM approval.',
            ]);
        }
    }

    /**
     * @return array{0: string, 1: int}
     */
    private function resolveAssigneeFromEntry(ShiftRosterEntry $entry): array
    {
        if ($entry->outsourced_employee_id) {
            return ['outsourced', (int) $entry->outsourced_employee_id];
        }

        if ($entry->employee_id) {
            return ['employee', (int) $entry->employee_id];
        }

        throw ValidationException::withMessages([
            'employee_id' => 'Unable to resolve the employee for this roster entry.',
        ]);
    }

    private function assertNoExistingOffDay(string $employeeType, int $employeeId, string $rosterDate): void
    {
        $query = ShiftRosterEntry::query()->where('roster_date', $rosterDate);

        if ($employeeType === 'outsourced') {
            $query->where('outsourced_employee_id', $employeeId);
        } else {
            $query->where('employee_id', $employeeId);
        }

        $existingEntry = $query->first();

        if ($existingEntry && strtolower((string) $existingEntry->status) === 'off') {
            throw ValidationException::withMessages([
                'roster_date' => 'This date is marked as off. Open the off day entry and convert it to assign a shift while keeping history.',
            ]);
        }
    }

    private function resolveAssigneeDisplayName(
        ShiftRosterApprovalRequest $request,
        ?ShiftRosterApprovalSegment $segment = null
    ): string {
        if ($request->request_type === 'roster') {
            $period = $request->start_date?->format('F Y') ?? 'Roster';

            if ($segment) {
                $departmentName = $segment->department?->name ?? 'Third-party';

                return $period . ' Roster — ' . $departmentName;
            }

            $employeeCount = $this->countAssigneesForRequest($request);

            return $period . ' Roster (' . $employeeCount . ' employees)';
        }

        if ($request->employee) {
            return trim((string) ($request->employee->full_name ?? $request->employee->first_name ?? 'Employee'));
        }

        if ($request->outsourcedEmployee) {
            return trim((string) ($request->outsourcedEmployee->full_name ?? 'Third-party employee'));
        }

        return 'Employee';
    }

    private function countAssigneesForRequest(ShiftRosterApprovalRequest $request): int
    {
        $entries = ShiftRosterEntry::query()
            ->where('shift_roster_approval_request_id', $request->id)
            ->get(['employee_id', 'outsourced_employee_id']);

        if ($entries->isEmpty()) {
            return 0;
        }

        return $entries
            ->groupBy(function (ShiftRosterEntry $entry) {
                if ($entry->employee_id) {
                    return 'employee:' . $entry->employee_id;
                }

                return 'outsourced:' . $entry->outsourced_employee_id;
            })
            ->count();
    }

    private function findPendingRosterRequestForPeriod(Carbon $periodStart, string $employeeGroup): ?ShiftRosterApprovalRequest
    {
        $shiftLabel = $periodStart->format('F Y') . ' Roster';
        if ($employeeGroup === 'third_party') {
            $shiftLabel .= ' (Third-party)';
        }

        return ShiftRosterApprovalRequest::query()
            ->where('approval_status', 'pending')
            ->where('request_type', 'roster')
            ->where('shift_label', $shiftLabel)
            ->first();
    }

    /**
     * @param Collection<int, ShiftRosterEntry> $entries
     */
    private function appendDraftsToPendingRosterRequest(
        ShiftRosterApprovalRequest $request,
        Collection $entries
    ): ShiftRosterApprovalRequest {
        return DB::transaction(function () use ($request, $entries) {
            $entries->load(['employee.department', 'outsourcedEmployee']);
            $request->load(['segments.department', 'segments.approverEmployee']);

            $departmentGroups = $entries->groupBy(function (ShiftRosterEntry $entry) {
                if ($entry->employee_id) {
                    return 'department:' . (int) ($entry->employee?->department_id ?? 0);
                }

                return 'outsourced:' . (int) ($entry->outsourcedEmployee?->sbu_id ?? 0);
            });

            $notifiedApprovers = [];

            foreach ($departmentGroups as $groupEntries) {
                $firstEntry = $groupEntries->first();
                $departmentId = $firstEntry->employee_id
                    ? ($firstEntry->employee?->department_id ? (int) $firstEntry->employee->department_id : null)
                    : null;

                $gm = $firstEntry->employee_id
                    ? $this->approverResolver->resolveGmForEmployee($firstEntry->employee)
                    : $this->approverResolver->resolveGmForOutsourcedEmployee($firstEntry->outsourcedEmployee);

                if ($gm === null) {
                    $departmentName = $firstEntry->employee?->department?->name ?? 'Third-party';
                    throw ValidationException::withMessages([
                        'approval' => 'No department head was found for ' . $departmentName . '. Roster cannot be submitted.',
                    ]);
                }

                $segment = $request->segments->first(function (ShiftRosterApprovalSegment $segment) use ($departmentId, $gm) {
                    return $segment->approval_status === 'pending'
                        && (int) ($segment->department_id ?? 0) === (int) ($departmentId ?? 0)
                        && (int) $segment->approver_employee_id === (int) $gm->id;
                });

                if (! $segment) {
                    $segment = $request->segments()->create([
                        'department_id' => $departmentId,
                        'approver_employee_id' => $gm->id,
                        'shift_count' => 0,
                        'off_day_count' => 0,
                        'employee_count' => 0,
                        'approval_status' => 'pending',
                    ]);
                }

                ShiftRosterEntry::query()
                    ->whereIn('id', $groupEntries->pluck('id')->all())
                    ->update([
                        'shift_roster_approval_request_id' => $request->id,
                        'shift_roster_approval_segment_id' => $segment->id,
                    ]);

                if (! in_array($gm->id, $notifiedApprovers, true)) {
                    $segment->load('department');
                    $this->notifier->notifyApprover(
                        $gm,
                        new ShiftRosterApprovalRequiredNotification($request, $segment)
                    );
                    $notifiedApprovers[] = $gm->id;
                }
            }

            $this->syncPendingRosterRequest($request->id);

            return $request->fresh(['segments.department', 'requestedByUser']);
        });
    }

    private function buildDurationLabel(ShiftRosterApprovalRequest $request): string
    {
        $days = $request->start_date && $request->end_date
            ? $request->start_date->diffInDays($request->end_date) + 1
            : 0;

        if ($request->request_type === 'roster') {
            return sprintf(
                '%d day(s) • %d shift(s) • %d off day(s) • %d employees',
                $days,
                (int) $request->shift_count,
                (int) $request->off_day_count,
                $this->countAssigneesForRequest($request)
            );
        }

        return sprintf(
            '%d day(s) • %d shift(s) • %d off day(s)',
            $days,
            (int) $request->shift_count,
            (int) $request->off_day_count
        );
    }

    private function buildDurationSummary(ShiftRosterApprovalRequest $request): string
    {
        if ($request->request_type === 'roster') {
            return sprintf(
                '%d shifts · %d off · %d employees',
                (int) $request->shift_count,
                (int) $request->off_day_count,
                $this->countAssigneesForRequest($request)
            );
        }

        return sprintf(
            '%d shifts · %d off',
            (int) $request->shift_count,
            (int) $request->off_day_count
        );
    }

    private function buildSegmentDurationLabel(
        ShiftRosterApprovalRequest $request,
        ShiftRosterApprovalSegment $segment
    ): string {
        $days = $request->start_date && $request->end_date
            ? $request->start_date->diffInDays($request->end_date) + 1
            : 0;

        return sprintf(
            '%d day(s) • %d shift(s) • %d off day(s) • %d employees',
            $days,
            (int) $segment->shift_count,
            (int) $segment->off_day_count,
            (int) $segment->employee_count
        );
    }

    private function buildSegmentDurationSummary(ShiftRosterApprovalSegment $segment): string
    {
        return sprintf(
            '%d shifts · %d off · %d employees',
            (int) $segment->shift_count,
            (int) $segment->off_day_count,
            (int) $segment->employee_count
        );
    }

    private function buildSegmentScopeLabel(
        ShiftRosterApprovalRequest $request,
        ShiftRosterApprovalSegment $segment
    ): string {
        $departmentName = $segment->department?->name ?? 'Third-party';

        return ($request->shift_label ?? 'Roster') . ' — ' . $departmentName
            . ' (' . (int) $segment->employee_count . ' employees)';
    }

    private function buildPeriodLabel(ShiftRosterApprovalRequest $request): string
    {
        if (! $request->start_date || ! $request->end_date) {
            return '-';
        }

        if ($request->start_date->isSameDay($request->end_date)) {
            return $request->start_date->format('d M Y');
        }

        return $request->start_date->format('d M') . ' – ' . $request->end_date->format('d M Y');
    }

    private function resolveShiftLabel(?int $shiftPlannerId, bool $isCustomTime): string
    {
        if ($isCustomTime) {
            return 'Custom shift';
        }

        if (! $shiftPlannerId) {
            return 'Shift roster';
        }

        return ShiftPlanner::query()->find($shiftPlannerId)?->name ?? 'Shift roster';
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    private function resolveShiftLabelFromItems(array $items): string
    {
        $firstShiftItem = collect($items)->firstWhere('entry_type', 'shift');

        if (! $firstShiftItem) {
            return 'Off days';
        }

        return $this->resolveShiftLabel(
            isset($firstShiftItem['shift_planner_id']) ? (int) $firstShiftItem['shift_planner_id'] : null,
            (bool) ($firstShiftItem['is_custom_time'] ?? false)
        );
    }

    private function buildItemFromEntry(ShiftRosterEntry $entry): array
    {
        $isOff = strtolower((string) $entry->status) === 'off';

        return [
            'roster_date' => $entry->roster_date->toDateString(),
            'entry_type' => $isOff ? 'off' : 'shift',
            'shift_planner_id' => $entry->shift_planner_id,
            'is_custom_time' => (bool) $entry->is_custom_time,
            'start_time' => $entry->start_time,
            'end_time' => $entry->end_time,
            'floor' => $entry->floor,
            'location_text' => $entry->location_text,
            'notes' => $entry->notes,
            'entry_status' => (string) $entry->status,
        ];
    }

    private function formatTimeForDisplay($value): string
    {
        if (! $value) {
            return '-';
        }

        return Carbon::parse($value)->format('h:i A');
    }

    private function normalizeOptionalString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private function resolveFirstReviewWeek(
        ShiftRosterApprovalRequest $request,
        ?ShiftRosterApprovalSegment $segment
    ): int {
        if (! $request->start_date) {
            return 1;
        }

        $monthStart = $request->start_date->copy()->startOfMonth();
        $firstWeekStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);

        $entryQuery = ShiftRosterEntry::query()
            ->where('shift_roster_approval_request_id', $request->id);

        if ($segment) {
            $entryQuery->where('shift_roster_approval_segment_id', $segment->id);
        }

        $firstDate = $entryQuery->orderBy('roster_date')->value('roster_date');
        if (! $firstDate) {
            return 1;
        }

        $weekStart = Carbon::parse($firstDate)->startOfWeek(Carbon::MONDAY);

        return max(1, (int) $firstWeekStart->diffInWeeks($weekStart) + 1);
    }

    private function resolveReviewEmployeeGroup(
        ShiftRosterApprovalRequest $request,
        ?ShiftRosterApprovalSegment $segment
    ): string {
        $entryQuery = ShiftRosterEntry::query()
            ->where('shift_roster_approval_request_id', $request->id);

        if ($segment) {
            $entryQuery->where('shift_roster_approval_segment_id', $segment->id);
        }

        $hasOutsourced = (clone $entryQuery)->whereNotNull('outsourced_employee_id')->exists();
        $hasInternal = (clone $entryQuery)->whereNotNull('employee_id')->exists();

        if ($hasOutsourced && ! $hasInternal) {
            return 'third_party';
        }

        return 'internal';
    }
}
