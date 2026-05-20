<?php

namespace App\Services\leaverequestPrivatefunctions;

use App\Models\EmployeLeaveRequest;
use App\Models\User;
use App\Notifications\LeaveApprovalRequestToHodNotification;
use App\Notifications\LeaveApprovedToHodNotification;
use App\Notifications\LeaveStatusUpdateNotification;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeaveRequestStatusHandler
{
    private const RECOMMENDATION_ACTION_TYPE = 1;

    private const FINAL_APPROVAL_ACTION_TYPE = 2;

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

        $leaveRequest->status = $newStatus;
        $leaveRequest->save();

        $actorName = Auth::user()->name ?? 'System';
        $statusLabel = $this->statusLabelForCode($newStatus);

        $this->logStatusChange($leaveRequest, $currentStatus, $newStatus, $statusLabel, $actorName);

        $requesterUser = $this->notifyRequester($leaveRequest, $actorName);

        $this->notifyHodAfterRecommendation($leaveRequest, $newStatus, $actorName);
        $this->notifyHodAfterApproval($leaveRequest, $newStatus, $actorName, $requesterUser);
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

        if ($this->userIsSuperAdmin($currentUser->id)) {
            return null;
        }

        $isAssigned = $leaveRequest->to_employee_id === optional($currentUser->employee)->id;

        if (! $isAssigned) {
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

    private function syncRelatedRequestStatuses(EmployeLeaveRequest $leaveRequest, int $newStatus): void
    {
        if ((int) $leaveRequest->action_type !== self::FINAL_APPROVAL_ACTION_TYPE) {
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

        EmployeLeaveRequest::where($match)
            ->where('action_type', self::FINAL_APPROVAL_ACTION_TYPE)
            ->where('id', '!=', $leaveRequest->id)
            ->where('status', 0)
            ->update(['status' => $newStatus]);

        EmployeLeaveRequest::where($match)
            ->where('action_type', self::RECOMMENDATION_ACTION_TYPE)
            ->where('status', '!=', $newStatus)
            ->update(['status' => $newStatus]);
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

    private function userIsSuperAdmin(int $userId): bool
    {
        return DB::table('user_roles')
            ->where('user_id', $userId)
            ->where('role_id', 1)
            ->exists();
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

    private function successResponse(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Status updated successfully.');
    }
}
