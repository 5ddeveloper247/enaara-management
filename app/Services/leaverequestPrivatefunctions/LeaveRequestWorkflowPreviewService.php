<?php

namespace App\Services\leaverequestPrivatefunctions;

use App\Models\Employee;

class LeaveRequestWorkflowPreviewService
{
    public function __construct(
        private LeaveRequestApproverResolver $approverResolver,
    ) {}

    public function previewForEmployee(Employee $employee): array
    {
        $employee->loadMissing(['role', 'department']);

        $recommenders = $this->approverResolver->resolveManagersForRecommendation($employee);
        $recommenders->load(['role']);
        $firstRecommender = $recommenders->first();

        $approvers = $this->approverResolver->resolveHodForFinalApproval($employee);
        $approvers->load(['role']);
        $firstApprover = $approvers->first();

        $steps = [];
        $level = 1;

        if ($firstRecommender) {
            $steps[] = $this->buildStep(
                $level++,
                'Leave Recommendation',
                $firstRecommender,
                true
            );
        }

        if ($firstApprover) {
            $steps[] = $this->buildStep(
                $level,
                'Leave Approval',
                $firstApprover,
                false
            );
        }

        return [
            'steps' => $steps,
            'recommendation_skipped' => $firstRecommender === null && $firstApprover !== null,
            'warning' => $this->resolveWarning($employee, $firstRecommender, $firstApprover),
        ];
    }

    private function buildStep(int $level, string $action, Employee $approver, bool $isRecommendation): array
    {
        return [
            'level' => $level,
            'action' => $action,
            'role_label' => $this->roleLabel($approver, $isRecommendation),
            'approver' => [
                'id' => $approver->id,
                'full_name' => $approver->full_name,
                'employee_code' => $approver->employee_code,
                'role_name' => $approver->role?->name,
            ],
        ];
    }

    private function roleLabel(Employee $employee, bool $isRecommendation): string
    {
        if ($isRecommendation && $employee->is_manager) {
            return 'Line Manager';
        }

        if ($employee->role?->name) {
            return $employee->role->name;
        }

        return $isRecommendation ? 'Supervisor' : 'Department Head';
    }

    private function resolveWarning(
        Employee $employee,
        ?Employee $firstRecommender,
        ?Employee $firstApprover
    ): ?string {
        if ($firstRecommender === null && $firstApprover === null) {
            if (empty($employee->role_id)) {
                return 'This employee has no role assigned. Approval routing cannot be determined.';
            }

            return 'No approvers could be resolved. Please verify department, role level, and line manager setup.';
        }

        if ($firstRecommender === null && $firstApprover !== null) {
            return 'No line manager found — this request will go directly to final approval.';
        }

        return null;
    }
}
