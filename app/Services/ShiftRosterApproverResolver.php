<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use App\Models\OutsourcedEmployee;
use App\Models\ShiftRosterEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ShiftRosterApproverResolver
{
    /** GM approver tier in role_levels.level */
    private const APPROVER_ROLE_LEVEL = 3;

    public function resolveGmForEmployee(Employee $employee): ?Employee
    {
        if (! empty($employee->department_id)) {
            $departmentApprover = $this->findApproverInScope([
                'organization_id' => $employee->organization_id,
                'sbu_id' => $employee->sbu_id,
                'department_id' => $employee->department_id,
            ], excludeEmployeeId: (int) $employee->id);

            if ($departmentApprover !== null) {
                return $departmentApprover;
            }

            return $this->resolveHumanResourceApprover([
                'organization_id' => $employee->organization_id,
                'sbu_id' => $employee->sbu_id,
            ], excludeEmployeeId: (int) $employee->id);
        }

        if (! empty($employee->sbu_id) || ! empty($employee->organization_id)) {
            $hrApprover = $this->resolveHumanResourceApprover([
                'organization_id' => $employee->organization_id,
                'sbu_id' => $employee->sbu_id,
            ], excludeEmployeeId: (int) $employee->id);

            if ($hrApprover !== null) {
                return $hrApprover;
            }
        }

        if (! empty($employee->organization_id)) {
            return $this->resolveHumanResourceApprover([
                'organization_id' => $employee->organization_id,
                'sbu_id' => null,
            ], excludeEmployeeId: (int) $employee->id);
        }

        return null;
    }

    public function resolveGmForOutsourcedEmployee(OutsourcedEmployee $outsourcedEmployee): ?Employee
    {
        $hrApprover = $this->resolveHumanResourceApprover([
            'organization_id' => $outsourcedEmployee->organization_id,
            'sbu_id' => $outsourcedEmployee->sbu_id,
        ]);

        if ($hrApprover !== null) {
            return $hrApprover;
        }

        if (! empty($outsourcedEmployee->organization_id)) {
            return $this->resolveHumanResourceApprover([
                'organization_id' => $outsourcedEmployee->organization_id,
                'sbu_id' => null,
            ]);
        }

        return null;
    }

    private function findApproverInScope(array $scope, ?int $excludeEmployeeId = null): ?Employee
    {
        $query = Employee::query()
            ->select('employees.*')
            ->join('roles as r', 'r.id', '=', 'employees.role_id')
            ->where('employees.is_active', true)
            ->where('r.is_active', true)
            ->where(function ($levelQuery) {
                $levelQuery->where(function ($fkQuery) {
                    $fkQuery->whereNotNull('r.role_level_id')
                        ->whereExists(function ($sub) {
                            $sub->selectRaw('1')
                                ->from('role_levels as rl')
                                ->whereColumn('rl.id', 'r.role_level_id')
                                ->where('rl.is_active', true)
                                ->where('rl.level', self::APPROVER_ROLE_LEVEL);
                        });
                })->orWhere(function ($nameQuery) {
                    $nameQuery->whereNull('r.role_level_id')
                        ->whereExists(function ($sub) {
                            $sub->selectRaw('1')
                                ->from('role_levels as rl')
                                ->whereRaw('LOWER(TRIM(rl.name)) = LOWER(TRIM(r.name))')
                                ->where('rl.is_active', true)
                                ->where('rl.level', self::APPROVER_ROLE_LEVEL);
                        });
                });
            });

        if (! empty($scope['organization_id'])) {
            $query->where('employees.organization_id', (int) $scope['organization_id']);
        }

        if (! empty($scope['sbu_id'])) {
            $query->where('employees.sbu_id', (int) $scope['sbu_id']);
        }

        if (! empty($scope['department_id'])) {
            $this->applyDepartmentScope($query, (int) $scope['department_id']);
        }

        if ($excludeEmployeeId) {
            $query->where('employees.id', '!=', $excludeEmployeeId);
        }

        return $query->orderBy('employees.id')->first();
    }

    private function applyDepartmentScope(Builder $query, int $departmentId): void
    {
        $query->where(function (Builder $deptQuery) use ($departmentId) {
            $deptQuery->where('employees.department_id', $departmentId)
                ->orWhereJsonContains('employees.department_ids', $departmentId)
                ->orWhereJsonContains('employees.department_ids', (string) $departmentId);
        });
    }

    /**
     * @param array{organization_id?: int|null, sbu_id?: int|null} $scope
     */
    private function resolveHumanResourceApprover(array $scope, ?int $excludeEmployeeId = null): ?Employee
    {
        $organizationId = ! empty($scope['organization_id']) ? (int) $scope['organization_id'] : null;
        $sbuId = ! empty($scope['sbu_id']) ? (int) $scope['sbu_id'] : null;

        foreach ($this->resolveHumanResourceDepartmentIds($organizationId, $sbuId) as $hrDepartmentId) {
            $approver = $this->findApproverInScope([
                'organization_id' => $organizationId,
                'sbu_id' => $sbuId,
                'department_id' => $hrDepartmentId,
            ], excludeEmployeeId: $excludeEmployeeId);

            if ($approver !== null) {
                return $approver;
            }
        }

        return null;
    }

    /**
     * @return array<int, int>
     */
    private function resolveHumanResourceDepartmentIds(?int $organizationId, ?int $sbuId): array
    {
        $query = Department::query()
            ->where('is_active', true)
            ->where(function (Builder $nameQuery) {
                $nameQuery->whereRaw('LOWER(TRIM(name)) = ?', ['human resource'])
                    ->orWhereRaw('LOWER(TRIM(name)) = ?', ['human resources']);
            });

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        if ($sbuId) {
            $query->where('sbu_id', $sbuId);
        }

        return $query
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function resolveGmForAssignee(string $assigneeType, int $assigneeId): ?Employee
    {
        if ($assigneeType === 'outsourced') {
            $outsourced = OutsourcedEmployee::query()->find($assigneeId);

            return $outsourced ? $this->resolveGmForOutsourcedEmployee($outsourced) : null;
        }

        $employee = Employee::query()->find($assigneeId);

        return $employee ? $this->resolveGmForEmployee($employee) : null;
    }

    public function resolveGmForRosterSubmission(?User $user, ?ShiftRosterEntry $sampleEntry = null): ?Employee
    {
        if ($user && $user->employee_id) {
            $employee = Employee::query()->find($user->employee_id);

            if ($employee) {
                $gm = $this->resolveGmForEmployee($employee);

                if ($gm !== null) {
                    return $gm;
                }
            }
        }

        if ($sampleEntry === null) {
            return null;
        }

        if ($sampleEntry->employee_id) {
            return $this->resolveGmForAssignee('employee', (int) $sampleEntry->employee_id);
        }

        if ($sampleEntry->outsourced_employee_id) {
            return $this->resolveGmForAssignee('outsourced', (int) $sampleEntry->outsourced_employee_id);
        }

        return null;
    }
}
