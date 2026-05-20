<?php

namespace App\Services\leaverequestPrivatefunctions;

use App\Models\Employee;
use App\Models\EmployeLeaveRequest;
use App\Models\User;
use App\Notifications\LeaveApprovalRequestToHodNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LeaveRequestSubmission
{
    private const MASTER_ACTION_TYPES = [0, 2];

    private const RECOMMENDATION_ACTION_TYPE = 1;

    private const FINAL_APPROVAL_ACTION_TYPE = 2;

    public function __construct(
        private LeaveRequestApproverResolver $leaveRequestApproverResolver,
        private LeaveRequestNotifier $leaveRequestNotifier,
    ) {}

    public function create(
        array $validated,
        Request $request,
        Employee $fromEmployee,
        Carbon $startDate,
        Carbon $endDate,
        float $duration
    ): EmployeLeaveRequest {
        $this->assertNoDateConflict($fromEmployee->id, $startDate, $endDate);

        $medicalReportPath = $this->storeMedicalReport($request);
        $basePayload = $this->buildBasePayload($validated, $fromEmployee, $startDate, $endDate, $duration, $medicalReportPath);

        $recommenders = $this->leaveRequestApproverResolver->resolveManagersForRecommendation($fromEmployee);
        $hodEmployees = $this->leaveRequestApproverResolver->resolveHodForFinalApproval($fromEmployee);

        $createdRequests = $this->createManagerRequests($recommenders, $basePayload);
        $finalLeaveRequests = $this->createHodRequests($hodEmployees, $basePayload);

        return $finalLeaveRequests->first()
            ?? $createdRequests[0]
            ?? new EmployeLeaveRequest();
    }

    private function assertNoDateConflict(int $employeeId, Carbon $startDate, Carbon $endDate): void
    {
        $hasConflict = EmployeLeaveRequest::where('from_employee_id', $employeeId)
            ->whereIn('status', [0, 1, 3])
            ->whereIn('action_type', self::MASTER_ACTION_TYPES)
            ->where('start_date', '<=', $endDate->toDateString())
            ->where('end_date', '>=', $startDate->toDateString())
            ->exists();

        if ($hasConflict) {
            throw ValidationException::withMessages([
                'start_date' => 'You already have a pending or approved leave request during this date range.',
            ]);
        }
    }

    private function storeMedicalReport(Request $request): ?string
    {
        if (! $request->hasFile('medical_report')) {
            return null;
        }

        return $request->file('medical_report')->store('leave-request/medical-reports', 'public');
    }

    private function buildBasePayload(
        array $validated,
        Employee $fromEmployee,
        Carbon $startDate,
        Carbon $endDate,
        float $duration,
        ?string $medicalReportPath
    ): array {
        return [
            'from_employee_id' => $fromEmployee->id,
            'from_user_id' => Auth::id(),
            'department_id' => $fromEmployee->department_id,
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'duration' => $duration,
            'reason' => $validated['reason'] ?? null,
            'medical_report' => $medicalReportPath,
            'status' => 0,
        ];
    }

    private function createManagerRequests(Collection $managers, array $basePayload): array
    {
        $created = [];

        foreach ($managers as $manager) {
            $managerUser = User::where('employee_id', $manager->id)->first();

            $leaveRequest = EmployeLeaveRequest::create(array_merge($basePayload, [
                'to_employee_id' => $manager->id,
                'to_user_id' => $managerUser?->id,
                'action_type' => self::RECOMMENDATION_ACTION_TYPE,
            ]));

            $this->loadLeaveRequestRelations($leaveRequest);
            $this->leaveRequestApproverResolver->notifyManager($leaveRequest, $manager);
            $created[] = $leaveRequest;
        }

        return $created;
    }

    private function createHodRequests(Collection $hodEmployees, array $basePayload): Collection
    {
        $finalLeaveRequests = collect();

        foreach ($hodEmployees as $hodEmployee) {
            $hodUser = User::where('employee_id', $hodEmployee->id)
                ->where('is_active', true)
                ->first();

            $finalLeaveRequest = EmployeLeaveRequest::create(array_merge($basePayload, [
                'to_employee_id' => $hodEmployee->id,
                'to_user_id' => $hodUser?->id,
                'action_type' => self::FINAL_APPROVAL_ACTION_TYPE,
            ]));

            $this->loadLeaveRequestRelations($finalLeaveRequest);

            $notification = (new LeaveApprovalRequestToHodNotification($finalLeaveRequest))
                ->delay(now()->addSeconds(5));

            $this->leaveRequestNotifier->notifyApprover($hodEmployee, $notification, true);

            $finalLeaveRequests->push($finalLeaveRequest);
        }

        return $finalLeaveRequests;
    }

    private function loadLeaveRequestRelations(EmployeLeaveRequest $leaveRequest): void
    {
        $leaveRequest->load([
            'fromEmployee:id,full_name',
            'toEmployee:id,full_name',
            'leaveType:id,name',
        ]);
    }
}
