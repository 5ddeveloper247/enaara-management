<?php

namespace App\Services\leaverequestPrivatefunctions;

use App\Models\Employee;
use App\Models\LeaveType;
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

    private function getLeaveTypesForAdminEmployee(Employee $employee): Collection
    {
        $organizationId = $employee->organization_id;
        $departmentId = $employee->department_id;

        return LeaveType::query()
            ->select(['id', 'name', 'code', 'leave_condition', 'annual_quota', 'organization_id', 'sbu_id', 'is_active'])
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
