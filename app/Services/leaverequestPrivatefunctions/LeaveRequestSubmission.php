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
        $isHalfDay = (bool) ($validated['is_half_day'] ?? false);

        $this->assertNoDateConflict($fromEmployee->id, $startDate, $endDate, $isHalfDay);

        $medicalReportPath = $this->storeMedicalReport($request);
        $basePayload = $this->buildBasePayload($validated, $fromEmployee, $startDate, $endDate, $duration, $medicalReportPath);

        $recommenders = $this->leaveRequestApproverResolver->resolveManagersForRecommendation($fromEmployee);
        $hodEmployees = $this->leaveRequestApproverResolver->resolveHodForFinalApproval($fromEmployee);

        $hodIds = $hodEmployees->pluck('id')->toArray();
        $recommenders = $recommenders->filter(function ($manager) use ($hodIds) {
            return !in_array($manager->id, $hodIds);
        });

        $createdRequests = $this->createManagerRequests($recommenders, $basePayload);
        $finalLeaveRequests = $this->createHodRequests($hodEmployees, $basePayload);

        return $finalLeaveRequests->first()
            ?? $createdRequests[0]
            ?? new EmployeLeaveRequest();
    }

    private function assertNoDateConflict(int $employeeId, Carbon $startDate, Carbon $endDate, bool $isHalfDay): void
    {
        $overlapping = EmployeLeaveRequest::query()
            ->where('from_employee_id', $employeeId)
            ->whereIn('status', [0, 1, 3])
            ->whereIn('action_type', self::MASTER_ACTION_TYPES)
            ->where('start_date', '<=', $endDate->toDateString())
            ->where('end_date', '>=', $startDate->toDateString())
            ->get(['id', 'is_half_day', 'start_date', 'end_date']);

        foreach ($overlapping as $existing) {
            if ($this->hasLeaveConflict($startDate, $endDate, $isHalfDay, $existing)) {
                throw ValidationException::withMessages([
                    'start_date' => 'You already have a pending or approved leave request during this date range.',
                ]);
            }
        }
    }

    private function hasLeaveConflict(
        Carbon $startDate,
        Carbon $endDate,
        bool $isHalfDay,
        EmployeLeaveRequest $existing
    ): bool {
        $existingStart = Carbon::parse($existing->start_date)->startOfDay();
        $existingEnd = Carbon::parse($existing->end_date)->startOfDay();

        $overlapStart = $startDate->copy()->startOfDay()->max($existingStart);
        $overlapEnd = $endDate->copy()->startOfDay()->min($existingEnd);

        if ($overlapStart->gt($overlapEnd)) {
            return false;
        }

        $existingIsHalfDay = (bool) $existing->is_half_day;

        if (! $isHalfDay || ! $existingIsHalfDay) {
            return true;
        }

        return true;
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
            'is_half_day' => (bool) ($validated['is_half_day'] ?? false),
            'half_day_session' => ($validated['is_half_day'] ?? false)
                ? ($validated['half_day_session'] ?? null)
                : null,
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
