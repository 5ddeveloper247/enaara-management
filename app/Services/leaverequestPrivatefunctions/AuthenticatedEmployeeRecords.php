<?php

namespace App\Services\leaverequestPrivatefunctions;

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthenticatedEmployeeRecords
{
    public function resolveAuthenticatedEmployee(): ?Employee
    {
        $user = Auth::user();

        if ($user === null) {
            return null;
        }

        return $user->employee;
    }

    public function getLeaveTypesForAuthenticatedEmployee(): Collection
    {
        $employee = $this->resolveAuthenticatedEmployee();

        if ($employee === null) {
            return collect();
        }

        return $this->getLeaveTypesForEmployee($employee);
    }

    public function getLeaveTypesForEmployee(Employee $employee): Collection
    {
        $organizationId = $employee->organization_id;
        $sbuId = $employee->sbu_id;

        if (empty($organizationId) || empty($sbuId)) {
            return collect();
        }

        $leaveTypeIdsForSbu = DB::table('leave_type_sbu')
            ->where('sbu_id', $sbuId)
            ->pluck('leave_type_id');

        return LeaveType::query()
            ->select(['id', 'name', 'code', 'leave_condition', 'annual_quota', 'organization_id', 'sbu_id', 'is_active'])
            ->with('setting:id,leave_type_id,short_leave_applicable')
            ->where('is_active', true)
            ->where('organization_id', $organizationId)
            ->where(function ($query) use ($sbuId, $leaveTypeIdsForSbu) {
                $query->where('sbu_id', $sbuId);

                if ($leaveTypeIdsForSbu->isNotEmpty()) {
                    $query->orWhereIn('id', $leaveTypeIdsForSbu);
                }
            })
            ->orderBy('name')
            ->get();
    }

    public function getLeaveTypesForQuotaSummary(Employee $employee): Collection
    {
        $authEmployee = $this->resolveAuthenticatedEmployee();

        if ($authEmployee && (int) $authEmployee->id === (int) $employee->id) {
            return $this->getLeaveTypesForEmployee($employee);
        }

        return $this->getLeaveTypesForAdminEmployee($employee);
    }

    public function getEmployeesForLeaveApplication(?User $user = null): Collection
    {
        $user = $user ?? Auth::user();

        if ($user === null) {
            return collect();
        }

        return $this->buildLeaveApplicationEmployeeQuery($user)
            ->with('department:id,name')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'employee_code', 'department_id']);
    }

    /**
     * @return array<string, \Illuminate\Support\Collection<int, Employee>>
     */
    public function getEmployeesGroupedForLeaveApplication(?User $user = null): array
    {
        $employees = $this->getEmployeesForLeaveApplication($user);
        $grouped = [];

        foreach ($employees as $employee) {
            $departmentName = trim((string) (optional($employee->department)->name ?? 'Unassigned'));
            $departmentName = $departmentName !== '' ? $departmentName : 'Unassigned';

            if (! isset($grouped[$departmentName])) {
                $grouped[$departmentName] = collect();
            }

            $grouped[$departmentName]->push($employee);
        }

        ksort($grouped, SORT_NATURAL | SORT_FLAG_CASE);

        return $grouped;
    }

    public function canApplyLeaveForEmployee(int $employeeId, ?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        if ($user === null) {
            return false;
        }

        if ($user->employee_id && (int) $user->employee_id === $employeeId) {
            return Employee::query()
                ->where('id', $employeeId)
                ->where('is_active', true)
                ->exists();
        }

        return $this->buildLeaveApplicationEmployeeQuery($user)
            ->where('id', $employeeId)
            ->exists();
    }

    private function buildLeaveApplicationEmployeeQuery(?User $user): Builder
    {
        $query = Employee::query()->where('is_active', true);

        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSystemAdminUser()) {
            return $query;
        }

        $viewerEmployee = $user->employee;

        if ($viewerEmployee === null) {
            return $query->whereRaw('1 = 0');
        }

        $viewerEmployee->loadMissing('department');

        if ($viewerEmployee->organization_id) {
            $query->where('organization_id', (int) $viewerEmployee->organization_id);
        }

        $sbuId = $viewerEmployee->sbu_id ? (int) $viewerEmployee->sbu_id : null;

        if (! $sbuId) {
            return $query->whereRaw('1 = 0');
        }

        $query->where('sbu_id', $sbuId);

        if ($this->isHumanResourceDepartment($viewerEmployee->department)) {
            return $query;
        }

        $departmentIds = $this->resolveViewerAllowedDepartmentIds($viewerEmployee);

        if ($departmentIds === []) {
            return $query->whereRaw('1 = 0');
        }

        $query->whereIn('department_id', $departmentIds);

        return $query;
    }

    /**
     * Departments assigned to the logged-in employee (primary + additional assignments).
     *
     * @return array<int, int>
     */
    private function resolveViewerAllowedDepartmentIds(Employee $employee): array
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

    private function getLeaveTypesForAdminEmployee(Employee $employee): Collection
    {
        $organizationId = $employee->organization_id;
        $departmentId = $employee->department_id;

        return LeaveType::query()
            ->select(['id', 'name', 'code', 'leave_condition', 'annual_quota', 'organization_id', 'sbu_id', 'is_active'])
            ->with('setting:id,leave_type_id,short_leave_applicable')
            ->where('is_active', true)
            ->when($organizationId, fn ($q) => $q->where(function ($qq) use ($organizationId) {
                $qq->whereNull('organization_id')->orWhere('organization_id', $organizationId);
            }))
            ->when($departmentId, fn ($q) => $q->where(function ($qq) use ($departmentId) {
                $qq->whereNull('department_id')->orWhere('department_id', $departmentId);
            }))
            ->orderBy('name')
            ->get();
    }
}
