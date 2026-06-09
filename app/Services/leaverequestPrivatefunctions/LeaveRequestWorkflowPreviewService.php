<?php

namespace App\Services\leaverequestPrivatefunctions;

use App\Models\Employee;
use Illuminate\Support\Collection;

class LeaveRequestWorkflowPreviewService
{
    public const TOP_LEVEL_MESSAGE = 'You hold the top-level role in the leave approval chain. There is no line manager or approver above you — no one will receive this request. Please contact HR to process your leave manually.';

    public function __construct(
        private LeaveRequestApproverResolver $approverResolver,
    ) {}

    public function previewForEmployee(Employee $employee): array
    {
        $employee->loadMissing(['role', 'department']);

        $recommenders = $this->loadEmployeesWithRoles(
            $this->approverResolver->resolveManagersForRecommendation($employee)
        );
        $approvers = $this->loadEmployeesWithRoles(
            $this->approverResolver->resolveHodForFinalApproval($employee)
        );

        $firstRecommender = $recommenders->first();
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

        $isTopLevel = $this->isTopLevelEmployee($employee, $firstRecommender, $firstApprover);

        return [
            'steps' => $steps,
            'recommendation_skipped' => $firstRecommender === null && $firstApprover !== null,
            'is_top_level' => $isTopLevel,
            'can_submit' => ! $isTopLevel && ($firstRecommender !== null || $firstApprover !== null),
            'top_level_message' => $isTopLevel ? self::TOP_LEVEL_MESSAGE : null,
            'warning' => $this->resolveWarning($employee, $firstRecommender, $firstApprover, $isTopLevel),
        ];
    }

    public function assertEmployeeCanSubmitLeave(Employee $employee): void
    {
        $preview = $this->previewForEmployee($employee);

        if ($preview['is_top_level']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'employee_id' => [self::TOP_LEVEL_MESSAGE],
            ]);
        }

        if (! $preview['can_submit']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'employee_id' => [$preview['warning'] ?? 'Leave approval routing could not be determined for this employee.'],
            ]);
        }
    }

    private function loadEmployeesWithRoles(Collection $employees): Collection
    {
        $ids = $employees->pluck('id')->filter()->unique()->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return Employee::query()
            ->with('role')
            ->whereIn('id', $ids->all())
            ->orderBy('id')
            ->get();
    }

    private function isTopLevelEmployee(
        Employee $employee,
        ?Employee $firstRecommender,
        ?Employee $firstApprover
    ): bool {
        if ($firstRecommender !== null || $firstApprover !== null) {
            return false;
        }

        if (empty($employee->role_id)) {
            return false;
        }

        return $this->approverResolver->resolveEmployeeRoleLevel($employee) !== null;
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
        ?Employee $firstApprover,
        bool $isTopLevel
    ): ?string {
        if ($isTopLevel) {
            return null;
        }

        if ($firstRecommender === null && $firstApprover === null) {
            if (empty($employee->role_id)) {
                return 'This employee has no role assigned. Approval routing cannot be determined.';
            }

            return 'No approvers could be resolved. Please verify department, role level, and line manager setup.';
        }

        if ($firstRecommender === null && $firstApprover !== null) {
            return 'No recommendation step — this request will go directly to the department head for approval.';
        }

        return null;
    }
}
