<?php

namespace App\Services\leaverequestPrivatefunctions;

use App\Models\Department;
use App\Models\EmployeLeaveRequest;
use App\Models\User;
use App\Notifications\LeaveApprovalRequestToHodNotification;
use App\Notifications\LeaveApprovedToHodNotification;
use App\Notifications\LeaveHrDelegatedActionNotification;
use App\Notifications\LeaveStatusUpdateNotification;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestStatusHandler
{
    private const RECOMMENDATION_ACTION_TYPE = 1;

    private const FINAL_APPROVAL_ACTION_TYPE = 2;

    private const DEPARTMENT_HEAD_ROLE_LEVEL = 3;

    public function __construct(
        private AuditTrailService $auditTrailService,
        private LeaveRequestApproverResolver $leaveRequestApproverResolver,
        private LeaveRequestNotifier $leaveRequestNotifier,
    ) {}

    public function handle(Request $request, int $leaveRequestId)
    {
        $request->validate([
            'status' => 'required|integer|in:0,1,2,3,4,5',
        ]);

        $leaveRequest = EmployeLeaveRequest::findOrFail($leaveRequestId);
        $newStatus = (int) $request->input('status');
        $currentStatus = (int) $leaveRequest->status;

        $denied = $this->authorizeStatusChange($request, $leaveRequest, $newStatus, $currentStatus);
        if ($denied !== null) {
            return $denied;
        }

        $currentUser = Auth::user();
        $isHrDelegatedAction = $this->canHumanResourceDelegateOnLeaveRequest($currentUser, $leaveRequest);

        // If the requester is cancelling a PENDING request, permanently delete all related rows.
        $isRequester = (int) $leaveRequest->from_employee_id === (int) optional(Auth::user()->employee)->id;
        if ($newStatus === 5 && $isRequester && $currentStatus === 0) {
            $this->deleteRelatedLeaveRequestRows($leaveRequest);
            return $this->successResponse($request, 'Leave request has been deleted successfully.');
        }

        $leaveRequest->status = $newStatus;

        $actorEmployee = $currentUser?->employee;
        if ($actorEmployee && in_array($newStatus, [1, 2, 3, 4, 5], true)) {
            $leaveRequest->acted_by_employee_id = $actorEmployee->id;
        }

        $leaveRequest->save();

        $actorName = Auth::user()->name ?? 'System';
        $statusLabel = $this->statusLabelForCode($newStatus);

        $this->logStatusChange($leaveRequest, $currentStatus, $newStatus, $statusLabel, $actorName);

        $requesterUser = $this->notifyRequester($leaveRequest, $actorName);

        $this->notifyHodAfterRecommendation($leaveRequest, $newStatus, $actorName);
        $this->notifyHodAfterApproval($leaveRequest, $newStatus, $actorName, $requesterUser);
        $this->notifyAssignedApproverOfHrDelegation($leaveRequest, $isHrDelegatedAction, $actorName);
        $this->syncRelatedRequestStatuses($leaveRequest, $newStatus);

        return $this->successResponse($request);
    }

    private function authorizeStatusChange(
        Request $request,
        EmployeLeaveRequest $leaveRequest,
        int $newStatus,
        int $currentStatus
    ) {
        $currentUser = Auth::user();

        $isAssigned = (int) $leaveRequest->to_employee_id === (int) optional($currentUser->employee)->id;
        $isRequester = (int) $leaveRequest->from_employee_id === (int) optional($currentUser->employee)->id;
        $isHrDelegate = $this->canHumanResourceDelegateOnLeaveRequest($currentUser, $leaveRequest);

        if ($newStatus === 5 && $isRequester) {
            // Allow cancellation if pending (0), recommended (1), not-recommended (2), or approved (3).
            if (in_array($currentStatus, [0, 1, 2, 3], true)) {
                return null;
            }
            return $this->deny($request, 'You can only cancel a request that is pending, recommended, or approved.');
        }

        if (
            $this->isHumanResourceViewer($currentUser)
            && ! $currentUser->isSystemAdminUser()
            && in_array($newStatus, [1, 2, 3, 4], true)
            && ! $this->canHumanResourceActOnLeaveRequest($currentUser, $leaveRequest)
            && ! $isHrDelegate
        ) {
            return $this->deny(
                $request,
                'As a Human Resource team member, you can view leave requests from other departments but cannot approve or reject them. Please contact the assigned manager or HOD.'
            );
        }

        if (! $isAssigned && ! $isHrDelegate) {
            return $this->deny($request, 'You do not have permission to act on this request.');
        }

        if ((int) $leaveRequest->action_type === self::RECOMMENDATION_ACTION_TYPE) {
            return $this->authorizeRecommendationChange($request, $currentStatus, $newStatus);
        }

        if ((int) $leaveRequest->action_type === self::FINAL_APPROVAL_ACTION_TYPE) {
            return $this->authorizeFinalApprovalChange($request, $currentStatus, $newStatus);
        }

        return $this->deny($request, 'This request has no valid action type assignment.');
    }

    private function authorizeRecommendationChange(Request $request, int $currentStatus, int $newStatus)
    {
        if (! in_array($currentStatus, [0, 1, 2], true)) {
            return $this->deny($request, 'You can only act on pending or recommended requests.');
        }

        if (! in_array($newStatus, [1, 2], true)) {
            return $this->deny($request, 'You can only recommend or not recommend.');
        }

        return null;
    }

    private function authorizeFinalApprovalChange(Request $request, int $currentStatus, int $newStatus)
    {
        if ($currentStatus !== 0) {
            return $this->deny($request, 'This final approval request has already been actioned.');
        }

        if (! in_array($newStatus, [3, 4, 5], true)) {
            return $this->deny($request, 'You can only approve, reject or cancel.');
        }

        return null;
    }

    private function logStatusChange(
        EmployeLeaveRequest $leaveRequest,
        int $currentStatus,
        int $newStatus,
        string $statusLabel,
        string $actorName
    ): void {
        $this->auditTrailService->log(
            action: $statusLabel,
            category: 'LeaveRequest',
            description: "Leave request #{$leaveRequest->id} for {$leaveRequest->fromEmployee->full_name} has been {$statusLabel} by {$actorName}.",
            auditable: $leaveRequest,
            context: ['old_status' => $currentStatus, 'new_status' => $newStatus]
        );
    }

    private function notifyRequester(EmployeLeaveRequest $leaveRequest, string $actorName): ?User
    {
        $requesterUser = User::where('id', $leaveRequest->from_user_id)->first();

        if ($requesterUser) {
            $requesterUser->notify(
                (new LeaveStatusUpdateNotification($leaveRequest, $actorName))
                    ->delay(now()->addSeconds(2))
            );
        }

        return $requesterUser;
    }

    private function notifyHodAfterRecommendation(
        EmployeLeaveRequest $leaveRequest,
        int $newStatus,
        string $actorName
    ): void {
        if ((int) $leaveRequest->action_type !== self::RECOMMENDATION_ACTION_TYPE || $newStatus !== 1) {
            return;
        }

        if (! $leaveRequest->fromEmployee) {
            return;
        }

        $finalRows = $this->findRelatedFinalApprovalRows($leaveRequest);

        foreach ($finalRows as $finalRow) {
            $notification = (new LeaveApprovalRequestToHodNotification($finalRow, $actorName))
                ->delay(now()->addSeconds(5));

            $this->leaveRequestNotifier->notifyEmployeeById(
                (int) $finalRow->to_employee_id,
                $notification,
                true
            );
        }
    }

    private function notifyHodAfterApproval(
        EmployeLeaveRequest $leaveRequest,
        int $newStatus,
        string $actorName,
        ?User $requesterUser
    ): void {
        if ((int) $leaveRequest->action_type !== self::FINAL_APPROVAL_ACTION_TYPE || $newStatus !== 3) {
            return;
        }

        $fromEmployee = $leaveRequest->fromEmployee;

        if (! $fromEmployee) {
            return;
        }

        $hodEmployees = $this->leaveRequestApproverResolver->resolveHodForFinalApproval($fromEmployee);

        foreach ($hodEmployees as $hodEmployee) {
            $hodUser = User::query()
                ->where('employee_id', $hodEmployee->id)
                ->where('is_active', true)
                ->first();

            if ($hodUser && $hodUser->id === $requesterUser?->id) {
                continue;
            }

            $notification = (new LeaveApprovedToHodNotification($leaveRequest, $actorName))
                ->delay(now()->addSeconds(6));

            $this->leaveRequestNotifier->notifyApprover($hodEmployee, $notification, true);
        }
    }

    private function notifyAssignedApproverOfHrDelegation(
        EmployeLeaveRequest $leaveRequest,
        bool $isHrDelegatedAction,
        string $hrActorName
    ): void {
        if (! $isHrDelegatedAction) {
            return;
        }

        $assignedEmployeeId = (int) $leaveRequest->to_employee_id;

        if ($assignedEmployeeId <= 0) {
            return;
        }

        $notification = (new LeaveHrDelegatedActionNotification($leaveRequest, $hrActorName))
            ->delay(now()->addSeconds(4));

        $this->leaveRequestNotifier->notifyEmployeeById($assignedEmployeeId, $notification, true);
    }

    private function syncRelatedRequestStatuses(EmployeLeaveRequest $leaveRequest, int $newStatus): void
    {
        if ((int) $leaveRequest->action_type !== self::FINAL_APPROVAL_ACTION_TYPE && $newStatus !== 5) {
            return;
        }

        if (! in_array($newStatus, [3, 4, 5], true)) {
            return;
        }

        $match = [
            ['from_employee_id', $leaveRequest->from_employee_id],
            ['leave_type_id', $leaveRequest->leave_type_id],
            ['start_date', $leaveRequest->start_date],
            ['end_date', $leaveRequest->end_date],
        ];

        if ($newStatus === 5 || $newStatus === 4) {
            // If cancelling or rejecting, force all related rows to cancelled/rejected.
            EmployeLeaveRequest::where($match)
                ->where('id', '!=', $leaveRequest->id)
                ->where('status', '!=', $newStatus)
                ->update(['status' => $newStatus]);

            // Gather all related request IDs (including main request) for entity cleanup
            $relatedRequestIds = EmployeLeaveRequest::where($match)->pluck('id')->all();

            // Refund quota.used for entities the daily cron already marked as claimed (status=1).
            // The booted() event handles the main request's entities first; this covers any siblings.
            $claimedByYear = \App\Models\EmployeLeaveEntity::whereIn('leave_request_id', $relatedRequestIds)
                ->where('status', 1)
                ->selectRaw('employee_id, leave_type_id, YEAR(leave_date) as leave_year, SUM(duration) as total_duration')
                ->groupBy('employee_id', 'leave_type_id', \Illuminate\Support\Facades\DB::raw('YEAR(leave_date)'))
                ->get();

            foreach ($claimedByYear as $row) {
                \App\Models\EmployeeLeaveQuota::where('employee_id', $row->employee_id)
                    ->where('leave_type_id', $row->leave_type_id)
                    ->where('year', (int) $row->leave_year)
                    ->where('used', '>', 0)
                    ->decrement('used', (float) $row->total_duration);
            }

            // Delete all remaining entities (status=0 not yet claimed)
            \App\Models\EmployeLeaveEntity::whereIn('leave_request_id', $relatedRequestIds)->delete();
        } else {
            EmployeLeaveRequest::where($match)
                ->where('action_type', self::FINAL_APPROVAL_ACTION_TYPE)
                ->where('id', '!=', $leaveRequest->id)
                ->whereIn('status', [0, 1, 2])
                ->update(['status' => $newStatus]);

            EmployeLeaveRequest::where($match)
                ->where('action_type', self::RECOMMENDATION_ACTION_TYPE)
                ->where('id', '!=', $leaveRequest->id)
                ->where('status', '!=', $newStatus)
                ->update(['status' => $newStatus]);
        }
    }

    /**
     * Permanently delete all related leave request rows for the same leave application.
     * Used when a requester cancels a still-pending request (no manager action taken yet).
     */
    private function deleteRelatedLeaveRequestRows(EmployeLeaveRequest $leaveRequest): void
    {
        EmployeLeaveRequest::where('from_employee_id', $leaveRequest->from_employee_id)
            ->where('leave_type_id', $leaveRequest->leave_type_id)
            ->where('start_date', $leaveRequest->start_date)
            ->where('end_date', $leaveRequest->end_date)
            ->delete();
    }

    private function findRelatedFinalApprovalRows(EmployeLeaveRequest $leaveRequest)
    {
        return EmployeLeaveRequest::where('from_employee_id', $leaveRequest->from_employee_id)
            ->where('leave_type_id', $leaveRequest->leave_type_id)
            ->where('start_date', $leaveRequest->start_date)
            ->where('end_date', $leaveRequest->end_date)
            ->where('action_type', self::FINAL_APPROVAL_ACTION_TYPE)
            ->get();
    }

    private function statusLabelForCode(int $statusCode): string
    {
        return match ($statusCode) {
            1 => 'recommended',
            2 => 'not recommended',
            3 => 'approved',
            4 => 'rejected',
            5 => 'cancelled',
            default => 'pending',
        };
    }

    private function deny(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], 403);
        }

        abort(403, $message);
    }

    private function successResponse(Request $request, string $message = 'Status updated successfully.')
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    private function isHumanResourceViewer(?User $user): bool
    {
        $user?->employee?->loadMissing('department');
        $department = $user?->employee?->department;

        if (! $department) {
            return false;
        }

        $normalized = strtolower(trim((string) $department->name));

        return in_array($normalized, ['human resource', 'human resources'], true);
    }

    private function canHumanResourceActOnLeaveRequest(?User $user, EmployeLeaveRequest $leaveRequest): bool
    {
        $viewerEmployee = $user?->employee;

        if (! $viewerEmployee) {
            return false;
        }

        $isAssignedApprover = (int) $leaveRequest->to_employee_id === (int) $viewerEmployee->id;

        if (! $isAssignedApprover) {
            return false;
        }

        $leaveRequest->loadMissing('fromEmployee');
        $viewerDepartmentId = $viewerEmployee->department_id ? (int) $viewerEmployee->department_id : null;
        $applicantDepartmentId = $leaveRequest->fromEmployee?->department_id
            ? (int) $leaveRequest->fromEmployee->department_id
            : null;

        return $viewerDepartmentId
            && $applicantDepartmentId
            && $viewerDepartmentId === $applicantDepartmentId;
    }

    private function canHumanResourceDelegateOnLeaveRequest(?User $user, EmployeLeaveRequest $leaveRequest): bool
    {
        if (! $this->isHrRoleLevelThreeViewer($user)) {
            return false;
        }

        $viewerEmployee = $user?->employee;

        if (! $viewerEmployee) {
            return false;
        }

        $leaveRequest->loadMissing('fromEmployee');
        $viewerDepartmentId = $viewerEmployee->department_id ? (int) $viewerEmployee->department_id : null;
        $applicantDepartmentId = $leaveRequest->fromEmployee?->department_id
            ? (int) $leaveRequest->fromEmployee->department_id
            : null;

        if (! $viewerDepartmentId || ! $applicantDepartmentId || $viewerDepartmentId === $applicantDepartmentId) {
            return false;
        }

        if ((int) $leaveRequest->action_type !== self::FINAL_APPROVAL_ACTION_TYPE) {
            return false;
        }

        $sbuId = $viewerEmployee->sbu_id ? (int) $viewerEmployee->sbu_id : null;

        if (! $sbuId) {
            return false;
        }

        return Department::query()
            ->where('id', $applicantDepartmentId)
            ->where('sbu_id', $sbuId)
            ->where('is_active', true)
            ->exists();
    }

    private function isHrRoleLevelThreeViewer(?User $user): bool
    {
        if (! $this->isHumanResourceViewer($user)) {
            return false;
        }

        $employee = $user?->employee;

        if (! $employee) {
            return false;
        }

        return $this->leaveRequestApproverResolver->resolveEmployeeRoleLevel($employee) === self::DEPARTMENT_HEAD_ROLE_LEVEL;
    }
}
