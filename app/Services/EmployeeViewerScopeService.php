<?php

namespace App\Services;

use App\Models\BiometricDevice;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\OutsourcedEmployee;
use App\Models\Sbu;
use App\Models\ThirdParty;
use App\Models\User;
use App\Services\ViewerScope\BiometricDeviceViewerScopeService;
use App\Services\ViewerScope\DepartmentViewerScopeService;
use App\Services\ViewerScope\DesignationViewerScopeService;
use App\Services\ViewerScope\ShiftViewerScopeService;
use App\Services\ViewerScope\SbuFloorViewerScopeService;
use App\Services\ViewerScope\ThirdPartyViewerScopeService;
use App\Services\ViewerScope\ViewerScopeContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class EmployeeViewerScopeService
{
    public function __construct(
        private readonly ViewerScopeContext $context,
        private readonly BiometricDeviceViewerScopeService $biometricDeviceScope,
        private readonly DepartmentViewerScopeService $departmentScope,
        private readonly DesignationViewerScopeService $designationScope,
        private readonly ShiftViewerScopeService $shiftScope,
        private readonly SbuFloorViewerScopeService $sbuFloorScope,
        private readonly ThirdPartyViewerScopeService $thirdPartyScope,
    ) {}

    public function resolveViewerSbuId(?User $user = null): ?int
    {
        return $this->context->resolveViewerSbuId($user);
    }

    public function resolveViewerOrganizationId(?User $user = null): ?int
    {
        return $this->context->resolveViewerOrganizationId($user);
    }

    public function isUnrestricted(?User $user = null): bool
    {
        return $this->context->isUnrestricted($user);
    }

    public function canManageEmployees(?User $user = null): bool
    {
        return $this->context->canManageEmployees($user);
    }

    public function frontendScopePayload(?User $user = null): array
    {
        return $this->context->frontendScopePayload($user);
    }

    public function applySbuScopeToEmployeeQuery(Builder $query, ?User $user = null): Builder
    {
        $sbuId = $this->context->resolveViewerSbuId($user);

        if ($sbuId === null) {
            return $query;
        }

        if ($sbuId <= 0) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('sbu_id', $sbuId);
    }

    /**
     * Restrict employee queries to departments the viewer may access.
     * System admin: no extra filter. HR: all active departments in SBU. Others: assigned departments only.
     */
    public function applyDepartmentScopeToEmployeeQuery(Builder $query, ?User $user = null): Builder
    {
        $departmentIds = $this->resolveViewerDepartmentIds($user);

        if ($departmentIds === null) {
            return $query;
        }

        if ($departmentIds === []) {
            return $query->whereRaw('1 = 0');
        }

        $this->applyEmployeeDepartmentIdsScope($query, $departmentIds);

        return $query;
    }

    /**
     * Apply SBU and department scope together (matches roster / leave application modules).
     */
    public function applyViewerScopeToEmployeeQuery(Builder $query, ?User $user = null): Builder
    {
        $this->applySbuScopeToEmployeeQuery($query, $user);
        $this->applyDepartmentScopeToEmployeeQuery($query, $user);

        return $query;
    }

    /**
     * @return array<int, int>|null null = unrestricted viewer (system admin)
     */
    public function resolveViewerDepartmentIds(?User $user = null): ?array
    {
        if ($this->context->isUnrestricted($user)) {
            return null;
        }

        $user = $user ?? Auth::user();
        $user?->loadMissing('employee.department');
        $viewerEmployee = $user?->employee;

        if ($viewerEmployee === null) {
            return [];
        }

        $viewerEmployee->loadMissing('department');
        $sbuId = $viewerEmployee->sbu_id ? (int) $viewerEmployee->sbu_id : null;

        if ($this->isHumanResourceDepartment($viewerEmployee->department)) {
            if (! $sbuId) {
                return [];
            }

            return Department::query()
                ->where('sbu_id', $sbuId)
                ->where('is_active', true)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
        }

        return $this->resolveEmployeeAssignedDepartmentIds($viewerEmployee);
    }

    public function applySbuScopeToDepartmentQuery(Builder $query, ?User $user = null): Builder
    {
        return $this->departmentScope->applyQueryScope($query, $user);
    }

    public function departmentBelongsToViewerScope(Department $department, ?User $user = null): bool
    {
        return $this->departmentScope->belongsToViewerScope($department, $user);
    }

    public function applySbuScopeToOutsourcedEmployeeQuery(Builder $query, ?User $user = null): Builder
    {
        $sbuId = $this->context->resolveViewerSbuId($user);

        if ($sbuId === null) {
            return $query;
        }

        if ($sbuId <= 0) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('sbu_id', $sbuId);
    }

    public function applySbuScopeToUserQuery(Builder $query, ?User $user = null): Builder
    {
        $sbuId = $this->context->resolveViewerSbuId($user);

        if ($sbuId === null) {
            return $query;
        }

        if ($sbuId <= 0) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas(
            'employee',
            fn (Builder $employeeQuery) => $employeeQuery->where('sbu_id', $sbuId)
        );
    }

    public function applySbuScopeToDesignationQuery(Builder $query, ?User $user = null): Builder
    {
        return $this->designationScope->applyQueryScope($query, $user);
    }

    public function designationBelongsToViewerScope(Designation $designation, ?User $user = null): bool
    {
        return $this->designationScope->belongsToViewerScope($designation, $user);
    }

    public function assertDesignationIdAccessible(int $designationId, ?User $user = null): void
    {
        $this->designationScope->assertIdAccessible($designationId, $user);
    }

    public function applySbuScopeToShiftPlannerQuery(Builder $query, ?User $user = null): Builder
    {
        return $this->shiftScope->applyPlannerQueryScope($query, $user);
    }

    public function applySbuScopeToShiftTypeQuery(Builder $query, ?User $user = null): Builder
    {
        return $this->shiftScope->applyShiftTypeQueryScope($query, $user);
    }

    public function assertShiftPlannerIdAccessible(int $shiftPlannerId, ?User $user = null): void
    {
        $this->shiftScope->assertPlannerIdAccessible($shiftPlannerId, $user);
    }

    public function assertShiftTypeIdAccessible(int $shiftTypeId, ?User $user = null): void
    {
        $this->shiftScope->assertShiftTypeIdAccessible($shiftTypeId, $user);
    }

    public function applySbuScopeToSbuFloorQuery(Builder $query, ?User $user = null): Builder
    {
        return $this->sbuFloorScope->applyQueryScope($query, $user);
    }

    public function applySbuScopeToBiometricDeviceQuery(Builder $query, ?User $user = null): Builder
    {
        return $this->biometricDeviceScope->applyQueryScope($query, $user);
    }

    public function biometricDeviceBelongsToViewerScope(BiometricDevice $device, ?User $user = null): bool
    {
        return $this->biometricDeviceScope->belongsToViewerScope($device, $user);
    }

    public function assertBiometricDeviceIdAccessible(int $deviceId, ?User $user = null): void
    {
        $this->biometricDeviceScope->assertIdAccessible($deviceId, $user);
    }

    public function assertSbuFloorIdAccessible(int $sbuFloorId, ?User $user = null): void
    {
        $this->sbuFloorScope->assertIdAccessible($sbuFloorId, $user);
    }

    public function applySbuScopeToThirdPartyQuery(Builder $query, ?User $user = null): Builder
    {
        return $this->thirdPartyScope->applyQueryScope($query, $user);
    }

    public function thirdPartyBelongsToViewerScope(ThirdParty $thirdParty, ?User $user = null): bool
    {
        return $this->thirdPartyScope->belongsToViewerScope($thirdParty, $user);
    }

    public function assertThirdPartyIdAccessible(int $thirdPartyId, ?User $user = null): void
    {
        $this->thirdPartyScope->assertIdAccessible($thirdPartyId, $user);
    }

    public function assertUserIdAccessible(int $userId, ?User $user = null): void
    {
        if ($this->context->isUnrestricted($user)) {
            return;
        }

        $viewerSbuId = $this->context->resolveViewerSbuId($user);
        if ($viewerSbuId <= 0) {
            throw ValidationException::withMessages([
                'user' => 'You are not authorized to access user accounts.',
            ]);
        }

        $targetUser = User::query()
            ->select(['id', 'employee_id'])
            ->with('employee:id,sbu_id')
            ->find($userId);

        if ($targetUser === null || $targetUser->employee === null) {
            throw ValidationException::withMessages([
                'user' => 'This user account is outside your SBU scope.',
            ]);
        }

        if ((int) ($targetUser->employee->sbu_id ?? 0) !== $viewerSbuId) {
            throw ValidationException::withMessages([
                'user' => 'This user account is outside your SBU scope.',
            ]);
        }
    }

    public function employeeBelongsToViewerScope(Employee $employee, ?User $user = null): bool
    {
        if ($this->context->isUnrestricted($user)) {
            return true;
        }

        $viewerSbuId = $this->context->resolveViewerSbuId($user);
        if ($viewerSbuId <= 0) {
            return false;
        }

        return (int) ($employee->sbu_id ?? 0) === $viewerSbuId;
    }

    public function assertEmployeeIdAccessible(int $employeeId, ?User $user = null): void
    {
        if ($this->context->isUnrestricted($user)) {
            return;
        }

        $viewerSbuId = $this->context->resolveViewerSbuId($user);
        if ($viewerSbuId <= 0) {
            throw ValidationException::withMessages([
                'employee' => 'You are not authorized to access employee records.',
            ]);
        }

        $employee = Employee::query()
            ->select(['id', 'sbu_id', 'department_id', 'department_ids'])
            ->find($employeeId);

        if ($employee === null || ! $this->employeeBelongsToViewerScope($employee, $user)) {
            throw ValidationException::withMessages([
                'employee' => 'This employee is outside your SBU scope.',
            ]);
        }

        if (! $this->employeeBelongsToViewerDepartmentScope($employee, $user)) {
            throw ValidationException::withMessages([
                'employee' => 'This employee is outside your department scope.',
            ]);
        }
    }

    public function employeeBelongsToViewerDepartmentScope(Employee $employee, ?User $user = null): bool
    {
        $departmentIds = $this->resolveViewerDepartmentIds($user);

        if ($departmentIds === null) {
            return true;
        }

        if ($departmentIds === []) {
            return false;
        }

        $employeeDepartmentIds = $this->resolveEmployeeAssignedDepartmentIds($employee);

        if (empty($employeeDepartmentIds)) {
            return true;
        }

        return count(array_intersect($employeeDepartmentIds, $departmentIds)) > 0;
    }

    public function assertSbuIdAllowed(?int $sbuId, ?User $user = null): void
    {
        if ($this->context->isUnrestricted($user)) {
            return;
        }

        $viewerSbuId = $this->context->resolveViewerSbuId($user);
        if ($viewerSbuId <= 0) {
            throw ValidationException::withMessages([
                'sbu_id' => 'You are not authorized to manage employees.',
            ]);
        }

        if ($sbuId === null || $sbuId <= 0 || (int) $sbuId !== $viewerSbuId) {
            throw ValidationException::withMessages([
                'sbu_id' => 'You can only manage employees for your SBU.',
            ]);
        }
    }

    public function assertDepartmentIdAllowed(int $departmentId, ?User $user = null): void
    {
        if ($this->context->isUnrestricted($user)) {
            return;
        }

        $viewerSbuId = $this->context->resolveViewerSbuId($user);
        if ($viewerSbuId <= 0) {
            throw ValidationException::withMessages([
                'department_id' => 'You are not authorized to manage employees.',
            ]);
        }

        $belongs = Department::query()
            ->whereKey($departmentId)
            ->where('sbu_id', $viewerSbuId)
            ->exists();

        if (! $belongs) {
            throw ValidationException::withMessages([
                'department_id' => 'The selected department is outside your SBU scope.',
            ]);
        }
    }

    public function assertDepartmentIdAccessible(int $departmentId, ?User $user = null): void
    {
        $this->assertDepartmentIdAllowed($departmentId, $user);

        $allowedDepartmentIds = $this->resolveViewerDepartmentIds($user);

        if ($allowedDepartmentIds === null) {
            return;
        }

        if (! in_array($departmentId, $allowedDepartmentIds, true)) {
            throw ValidationException::withMessages([
                'department_id' => 'The selected department is outside your department scope.',
            ]);
        }
    }

    /**
     * @param  array<int, int>  $departmentIds
     */
    public function assertDepartmentIdsAllowed(array $departmentIds, ?User $user = null): void
    {
        $departmentIds = array_values(array_unique(array_filter(array_map('intval', $departmentIds))));
        if ($departmentIds === []) {
            return;
        }

        foreach ($departmentIds as $departmentId) {
            $this->assertDepartmentIdAllowed($departmentId, $user);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function applyDefaultOrganizationSbuToRegistrationData(array &$data, ?User $user = null): void
    {
        if ($this->context->isUnrestricted($user)) {
            return;
        }

        $scope = $this->context->frontendScopePayload($user);
        if (! $scope['restricted'] || empty($scope['sbu_id'])) {
            return;
        }

        $data['organization_id'] = $scope['organization_id'];
        $data['sbu_id'] = $scope['sbu_id'];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function assertRegistrationDataAllowed(array $data, ?User $user = null): void
    {
        if ($this->context->isUnrestricted($user)) {
            return;
        }

        $scope = $this->context->frontendScopePayload($user);
        if (! $scope['restricted']) {
            return;
        }

        if (empty($scope['sbu_id'])) {
            throw ValidationException::withMessages([
                'sbu_id' => 'Your account is not linked to an SBU. You cannot register employees.',
            ]);
        }

        if (! empty($data['organization_id']) && (int) $data['organization_id'] !== (int) $scope['organization_id']) {
            throw ValidationException::withMessages([
                'organization_id' => 'You can only register employees for your organization.',
            ]);
        }

        if (array_key_exists('sbu_id', $data) && $data['sbu_id'] !== null && $data['sbu_id'] !== '') {
            $this->assertSbuIdAllowed((int) $data['sbu_id'], $user);
        }

        $departmentIds = [];
        if (! empty($data['department_ids']) && is_array($data['department_ids'])) {
            $departmentIds = array_map('intval', $data['department_ids']);
        } elseif (! empty($data['department_id'])) {
            $departmentIds = [(int) $data['department_id']];
        }

        $this->assertDepartmentIdsAllowed($departmentIds, $user);
    }

    /**
     * @param  Collection<int, Organization>  $organizations
     * @return Collection<int, Organization>
     */
    public function filterOrganizations(Collection $organizations, ?User $user = null): Collection
    {
        if ($this->context->isUnrestricted($user)) {
            return $organizations;
        }

        $scope = $this->context->frontendScopePayload($user);
        if (empty($scope['organization_id']) || empty($scope['sbu_id'])) {
            return collect();
        }

        return $organizations
            ->filter(fn (Organization $organization) => (int) $organization->id === (int) $scope['organization_id'])
            ->map(function (Organization $organization) use ($scope) {
                if ($organization->relationLoaded('sbus')) {
                    $organization->setRelation(
                        'sbus',
                        $organization->sbus
                            ->filter(fn (Sbu $sbu) => (int) $sbu->id === (int) $scope['sbu_id'])
                            ->values()
                    );
                }

                foreach ($organization->sbus as $sbu) {
                    if ($sbu->relationLoaded('departments')) {
                        $sbu->setRelation(
                            'departments',
                            $sbu->departments
                                ->filter(fn (Department $department) => (int) $department->sbu_id === (int) $scope['sbu_id'])
                                ->values()
                        );
                    }

                    if ($sbu->relationLoaded('floors')) {
                        $sbu->setRelation(
                            'floors',
                            $sbu->floors
                                ->filter(fn ($floor) => (int) $floor->sbu_id === (int) $scope['sbu_id'])
                                ->values()
                        );
                    }
                }

                return $organization;
            })
            ->values();
    }

    /**
     * @param  Collection<int, Sbu>  $sbus
     * @return Collection<int, Sbu>
     */
    public function filterSbus(Collection $sbus, ?User $user = null): Collection
    {
        $sbuId = $this->context->resolveViewerSbuId($user);
        if ($sbuId === null) {
            return $sbus;
        }
        if ($sbuId <= 0) {
            return collect();
        }

        return $sbus->filter(fn (Sbu $sbu) => (int) $sbu->id === $sbuId)->values();
    }

    /**
     * @param  Collection<int, Department>  $departments
     * @return Collection<int, Department>
     */
    public function filterDepartments(Collection $departments, ?User $user = null): Collection
    {
        $sbuId = $this->context->resolveViewerSbuId($user);
        if ($sbuId === null) {
            return $departments;
        }
        if ($sbuId <= 0) {
            return collect();
        }

        $departments = $departments
            ->filter(fn (Department $department) => (int) $department->sbu_id === $sbuId)
            ->values();

        $allowedDepartmentIds = $this->resolveViewerDepartmentIds($user);

        if ($allowedDepartmentIds === null) {
            return $departments;
        }

        if ($allowedDepartmentIds === []) {
            return collect();
        }

        $allowed = array_flip($allowedDepartmentIds);

        return $departments
            ->filter(fn (Department $department) => isset($allowed[(int) $department->id]))
            ->values();
    }

    /**
     * @param  array<int, int>  $departmentIds
     */
    private function applyEmployeeDepartmentIdsScope(Builder $query, array $departmentIds): void
    {
        $query->where(function ($departmentQuery) use ($departmentIds) {
            $departmentQuery->whereIn('department_id', $departmentIds);

            foreach ($departmentIds as $departmentId) {
                $departmentQuery->orWhere(function ($jsonQuery) use ($departmentId) {
                    $jsonQuery->whereJsonContains('department_ids', $departmentId)
                        ->orWhereJsonContains('department_ids', (string) $departmentId);
                });
            }
        });
    }

    /**
     * @return array<int, int>
     */
    private function resolveEmployeeAssignedDepartmentIds(Employee $employee): array
    {
        $departmentIds = [];

        if ($employee->department_id) {
            $departmentIds[] = (int) $employee->department_id;
        }

        if (is_array($employee->department_ids)) {
            foreach ($employee->department_ids as $departmentId) {
                if ($departmentId !== null && $departmentId !== '') {
                    $departmentIds[] = (int) $departmentId;
                }
            }
        }

        return array_values(array_unique(array_filter($departmentIds)));
    }

    private function isHumanResourceDepartment(?Department $department): bool
    {
        if (! $department) {
            return false;
        }

        $normalized = strtolower(trim((string) $department->name));

        return in_array($normalized, ['human resource', 'human resources'], true);
    }

    /**
     * @param  Collection<int, \App\Models\SbuFloor>  $floors
     */
    public function filterFloors(Collection $floors, ?User $user = null): Collection
    {
        $sbuId = $this->context->resolveViewerSbuId($user);
        if ($sbuId === null) {
            return $floors;
        }
        if ($sbuId <= 0) {
            return collect();
        }

        return $floors->filter(fn ($floor) => (int) $floor->sbu_id === $sbuId)->values();
    }

    /**
     * @param  array<int, array<string, mixed>>  $orgsData
     * @return array<int, array<string, mixed>>
     */
    public function filterOrgsData(array $orgsData, ?User $user = null): array
    {
        if ($this->context->isUnrestricted($user)) {
            return $orgsData;
        }

        $scope = $this->context->frontendScopePayload($user);
        if (empty($scope['organization_id']) || empty($scope['sbu_id'])) {
            return [];
        }

        return collect($orgsData)
            ->filter(fn (array $org) => (int) ($org['id'] ?? 0) === (int) $scope['organization_id'])
            ->map(function (array $org) use ($scope) {
                $org['sbus'] = collect($org['sbus'] ?? [])
                    ->filter(fn (array $sbu) => (int) ($sbu['id'] ?? 0) === (int) $scope['sbu_id'])
                    ->values()
                    ->all();

                return $org;
            })
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, \App\Models\Role>|\Illuminate\Support\Collection<int, array<string, mixed>>  $rolesData
     */
    public function filterRolesData($rolesData, ?User $user = null)
    {
        if ($this->context->isUnrestricted($user)) {
            return $rolesData;
        }

        $scope = $this->context->frontendScopePayload($user);
        if (empty($scope['organization_id']) || empty($scope['sbu_id'])) {
            return collect();
        }

        return collect($rolesData)->filter(function ($role) use ($scope) {
            $organizationId = (int) (is_array($role) ? ($role['organization_id'] ?? 0) : ($role->organization_id ?? 0));
            if ($organizationId !== (int) $scope['organization_id']) {
                return false;
            }

            if (is_array($role) ? ! empty($role['is_organization_level']) : $role->isOrganizationLevelRole()) {
                return true;
            }

            $roleSbuId = (int) (is_array($role) ? ($role['sbu_id'] ?? 0) : ($role->sbu_id ?? 0));
            if ($roleSbuId === (int) $scope['sbu_id']) {
                return true;
            }

            $linkedSbuIds = is_array($role)
                ? ($role['linked_sbu_ids'] ?? [])
                : ($role['linked_sbu_ids'] ?? []);

            return in_array((int) $scope['sbu_id'], array_map('intval', (array) $linkedSbuIds), true);
        })->values();
    }
}
