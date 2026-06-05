<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\OutsourcedEmployee;
use App\Models\ShiftPlanner;
use App\Models\ShiftRosterApprovalRequest;
use App\Models\ShiftRosterApprovalRequestItem;
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
     * @param array<int, array<string, mixed>> $items
     */
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
                ->with(['items', 'employee', 'outsourcedEmployee'])
                ->findOrFail($requestId);

            if (! $request->isPending()) {
                throw ValidationException::withMessages([
                    'approval' => 'This roster request has already been processed.',
                ]);
            }

            $this->assertUserCanApprove($request, $userId ?? Auth::id());

            foreach ($request->items as $item) {
                $this->applyApprovedItem($request, $item, $userId ?? Auth::id());
            }

            $request->update([
                'approval_status' => 'approved',
                'approved_by' => $userId ?? Auth::id(),
                'approved_at' => now(),
            ]);

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

            $this->assertUserCanApprove($request, $userId ?? Auth::id());

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

    public function getPendingForApprover(?int $approverEmployeeId): Collection
    {
        if (! $approverEmployeeId) {
            return collect();
        }

        return ShiftRosterApprovalRequest::query()
            ->with(['employee.department', 'outsourcedEmployee', 'requestedByUser'])
            ->where('approval_status', 'pending')
            ->where('approver_employee_id', $approverEmployeeId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function formatPendingListItem(ShiftRosterApprovalRequest $request): array
    {
        $assigneeName = $this->resolveAssigneeDisplayName($request);
        $words = preg_split('/\s+/u', trim($assigneeName), 2, PREG_SPLIT_NO_EMPTY) ?: [];
        $initials = strtoupper(substr($words[0] ?? 'E', 0, 1) . substr($words[1] ?? '', 0, 1));

        return [
            'id' => $request->id,
            'name' => $assigneeName,
            'initials' => $initials !== '' ? $initials : 'E',
            'department' => $request->employee?->department?->name ?? '-',
            'requested_by' => $request->requestedByUser?->name ?? 'Unknown',
            'request_date' => $request->created_at?->format('M d, Y') ?? '-',
            'start_date' => $request->start_date?->format('M d, Y') ?? '-',
            'end_date' => $request->end_date?->format('M d, Y') ?? '-',
            'shift_count' => (int) $request->shift_count,
            'off_day_count' => (int) $request->off_day_count,
            'shift_label' => $request->shift_label ?? 'Shift roster',
            'duration_label' => $this->buildDurationLabel($request),
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
            ])
            ->findOrFail($requestId);

        if ($viewerEmployeeId && (int) $request->approver_employee_id !== (int) $viewerEmployeeId) {
            $user = Auth::user();
            if (! $user || ! $user->isSystemAdminUser()) {
                throw ValidationException::withMessages([
                    'approval' => 'You are not authorized to view this roster request.',
                ]);
            }
        }

        $assigneeName = $this->resolveAssigneeDisplayName($request);
        $words = preg_split('/\s+/u', trim($assigneeName), 2, PREG_SPLIT_NO_EMPTY) ?: [];
        $initials = strtoupper(substr($words[0] ?? 'E', 0, 1) . substr($words[1] ?? '', 0, 1));

        $items = $request->items
            ->sortBy(fn (ShiftRosterApprovalRequestItem $item) => $item->roster_date?->format('Y-m-d'))
            ->values();

        return [
            'id' => $request->id,
            'approval_status' => $request->approval_status,
            'request_type' => $request->request_type,
            'assignee_name' => $assigneeName,
            'assignee_initials' => $initials !== '' ? $initials : 'E',
            'department' => $request->employee?->department?->name ?? '-',
            'requested_by' => $request->requestedByUser?->name ?? 'Unknown',
            'requested_by_role' => $request->requestedByUser?->employee?->role?->name ?? '-',
            'approver_name' => $request->approverEmployee?->full_name ?? $request->approverEmployee?->first_name ?? '-',
            'approver_role' => $request->approverEmployee?->role?->name ?? '-',
            'start_date' => $request->start_date?->format('d M'),
            'end_date' => $request->end_date?->format('d M Y'),
            'period_label' => $this->buildPeriodLabel($request),
            'shift_count' => (int) $request->shift_count,
            'off_day_count' => (int) $request->off_day_count,
            'shift_label' => $request->shift_label ?? 'Shift roster',
            'duration_label' => $this->buildDurationSummary($request),
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

        if ((int) $user->employee_id !== (int) $request->approver_employee_id) {
            throw ValidationException::withMessages([
                'approval' => 'You are not authorized to approve this roster request.',
            ]);
        }
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

    private function resolveAssigneeDisplayName(ShiftRosterApprovalRequest $request): string
    {
        if ($request->employee) {
            return trim((string) ($request->employee->full_name ?? $request->employee->first_name ?? 'Employee'));
        }

        if ($request->outsourcedEmployee) {
            return trim((string) ($request->outsourcedEmployee->full_name ?? 'Third-party employee'));
        }

        return 'Employee';
    }

    private function buildDurationLabel(ShiftRosterApprovalRequest $request): string
    {
        $days = $request->start_date && $request->end_date
            ? $request->start_date->diffInDays($request->end_date) + 1
            : 0;

        return sprintf(
            '%d day(s) • %d shift(s) • %d off day(s)',
            $days,
            (int) $request->shift_count,
            (int) $request->off_day_count
        );
    }

    private function buildDurationSummary(ShiftRosterApprovalRequest $request): string
    {
        return sprintf(
            '%d shifts · %d off',
            (int) $request->shift_count,
            (int) $request->off_day_count
        );
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
}
