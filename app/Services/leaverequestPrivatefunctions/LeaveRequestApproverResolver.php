<?php

namespace App\Services\leaverequestPrivatefunctions;

use App\Models\Employee;
use App\Models\EmployeLeaveRequest;
use App\Models\Role;
use App\Notifications\LeaveApprovalNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class LeaveRequestApproverResolver
{
    public function __construct(
        private LeaveRequestNotifier $leaveRequestNotifier,
    ) {}

    public function resolveManagersForRecommendation(Employee $employee): Collection
    {
        $currentLevel = $this->resolveEmployeeRoleLevel($employee);

        if ($currentLevel === null) {
            return collect();
        }

        $hodIds = $this->resolveHodForFinalApproval($employee)->pluck('id')->all();

        if ($this->departmentHasHodTierLineManager($employee, $hodIds)) {
            return collect();
        }

        foreach ($this->buildApprovalScopes($employee) as $scope) {
            $lineManagers = $this->findLineManagersInScope($scope, $employee->id)
                ->filter(fn (Employee $manager) => ! in_array($manager->id, $hodIds, true))
                ->values();

            if ($lineManagers->isNotEmpty()) {
                return $lineManagers;
            }

            $belowHod = $this->findRecommendersImmediatelyBelowHodInScope($employee, $currentLevel, $scope);
            if ($belowHod->isNotEmpty()) {
                return $belowHod;
            }
        }

        return collect();
    }

    public function resolveHodForFinalApproval(Employee $employee): Collection
    {
        return $this->resolveApprovers($employee, 'min');
    }

    public function notifyManager(EmployeLeaveRequest $leaveRequest, ?Employee $manager): void
    {
        if ($manager === null) {
            return;
        }

        $this->leaveRequestNotifier->notifyApprover(
            $manager,
            new LeaveApprovalNotification($leaveRequest)
        );
    }

    private function resolveApprovers(Employee $employee, string $levelStrategy): Collection
    {
        $currentLevel = $this->resolveEmployeeRoleLevel($employee);

        if ($currentLevel === null) {
            return collect();
        }

        foreach ($this->buildApprovalScopes($employee) as $scope) {
            $rows = $this->findEmployeesInScope($employee, $currentLevel, $scope, $levelStrategy);

            if ($rows->isNotEmpty()) {
                return $rows;
            }
        }

        return collect();
    }

    private function departmentHasHodTierLineManager(Employee $employee, array $hodIds): bool
    {
        if (empty($employee->department_id) || $hodIds === []) {
            return false;
        }

        $scope = [
            'organization_id' => $employee->organization_id,
            'sbu_id' => $employee->sbu_id,
            'department_id' => $employee->department_id,
        ];

        return $this->findLineManagersInScope($scope)
            ->contains(fn (Employee $manager) => in_array($manager->id, $hodIds, true));
    }

    private function findLineManagersInScope(array $scope, ?int $excludeEmployeeId = null): Collection
    {
        if (empty($scope['department_id'])) {
            return collect();
        }

        $query = Employee::query()
            ->where('is_active', true)
            ->where('is_manager', true)
            ->where('department_id', (int) $scope['department_id']);

        if ($excludeEmployeeId !== null) {
            $query->where('id', '!=', $excludeEmployeeId);
        }

        if (! empty($scope['organization_id'])) {
            $query->where('organization_id', (int) $scope['organization_id']);
        }

        if (! empty($scope['sbu_id'])) {
            $query->where('sbu_id', (int) $scope['sbu_id']);
        }

        return $query
            ->orderBy('id')
            ->get()
            ->unique('id')
            ->values();
    }

    private function findRecommendersImmediatelyBelowHodInScope(
        Employee $employee,
        int $currentLevel,
        array $scope
    ): Collection {
        $base = $this->buildApproversBaseQuery($employee, $currentLevel, $scope);

        $hodLevel = (clone $base)->min('rl.level');
        if ($hodLevel === null) {
            return collect();
        }

        $recommenderLevel = (clone $base)
            ->where('rl.level', '>', (int) $hodLevel)
            ->max('rl.level');

        if ($recommenderLevel === null) {
            return collect();
        }

        return $base
            ->where('rl.level', (int) $recommenderLevel)
            ->orderBy('employees.id')
            ->get()
            ->unique('id')
            ->values();
    }

    private function resolveEmployeeRoleLevel(Employee $employee): ?int
    {
        $roleId = (int) ($employee->role_id ?? 0);

        if ($roleId <= 0) {
            return null;
        }

        $level = Role::query()
            ->from('roles as r')
            ->join('role_levels as rl', 'rl.name', '=', 'r.name')
            ->where('r.id', $roleId)
            ->value('rl.level');

        return $level === null ? null : (int) $level;
    }

    private function buildApprovalScopes(Employee $employee): array
    {
        $scopes = [];

        if (! empty($employee->department_id)) {
            $scopes[] = [
                'organization_id' => $employee->organization_id,
                'sbu_id' => $employee->sbu_id,
                'department_id' => $employee->department_id,
            ];
        }

        if (! empty($employee->sbu_id)) {
            $scopes[] = [
                'organization_id' => $employee->organization_id,
                'sbu_id' => $employee->sbu_id,
                'department_id' => null,
            ];
        }

        if (! empty($employee->organization_id)) {
            $scopes[] = [
                'organization_id' => $employee->organization_id,
                'sbu_id' => null,
                'department_id' => null,
            ];
        }

        return $scopes;
    }

    private function buildApproversBaseQuery(
        Employee $employee,
        int $currentLevel,
        array $scope
    ): Builder {
        $base = Employee::query()
            ->select('employees.*')
            ->join('roles as r', 'r.id', '=', 'employees.role_id')
            ->join('role_levels as rl', 'rl.name', '=', 'r.name')
            ->where('employees.is_active', true)
            ->where('employees.id', '!=', $employee->id)
            ->where('rl.level', '<', $currentLevel);

        if (! empty($scope['organization_id'])) {
            $base->where('employees.organization_id', (int) $scope['organization_id']);
        }

        if (! empty($scope['sbu_id'])) {
            $base->where('employees.sbu_id', (int) $scope['sbu_id']);
        }

        if (! empty($scope['department_id'])) {
            $base->where('employees.department_id', (int) $scope['department_id']);
        }

        return $base;
    }

    private function findEmployeesInScope(
        Employee $employee,
        int $currentLevel,
        array $scope,
        string $levelStrategy
    ): Collection {
        $base = $this->buildApproversBaseQuery($employee, $currentLevel, $scope);

        $targetLevel = $levelStrategy === 'min'
            ? (clone $base)->min('rl.level')
            : (clone $base)->max('rl.level');

        if ($targetLevel === null) {
            return collect();
        }

        return $base
            ->where('rl.level', (int) $targetLevel)
            ->orderBy('employees.id')
            ->get()
            ->unique('id')
            ->values();
    }
}
