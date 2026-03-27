<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\PublicHoliday;
use App\Models\EmployeLeaveEntity;
use App\Models\Employee;
use App\Models\Department;
use App\Models\EmployeeLeaveQuota;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeaveCalenderService
{
    public function index()
    {
        $organizations = Organization::where('is_active', true)->orderBy('name')->get();
        $publicHolidays = PublicHoliday::with('organizations')->orderBy('start_date')->get();

        return view('admin.leave-calendar.index', compact('organizations', 'publicHolidays'));
    }

    public function getBlackoutDates($startDate = null, $endDate = null)
    {
        $query = PublicHoliday::where('is_blackout', 1);
        if ($startDate)
            $query->where('start_date', '>=', $startDate);
        if ($endDate)
            $query->where('end_date', '<=', $endDate);

        return $query->get()->map(function ($holiday) {
            return [
                'date' => $holiday->start_date->toDateString(),
                'reason' => $holiday->reason ?? $holiday->name
            ];
        });
    }

    public function getDepartmentLeaves($startDate, $endDate)
    {
        // Get counts from EmployeLeaveEntity (used leaves - status 1)
        $leaves = EmployeLeaveEntity::with('employee.department')
            ->whereBetween('leave_date', [$startDate, $endDate])
            ->where('status', 1) // Only show Used/Taken leaves
            ->select('leave_date', 'department_id', DB::raw('count(*) as count'))
            ->groupBy('leave_date', 'department_id')
            ->get();

        // Get total staff per department directly from employees table
        $departmentStaffCounts = Employee::select('department_id', DB::raw('count(*) as total'))
            ->groupBy('department_id')
            ->pluck('total', 'department_id');

        $result = [];
        foreach ($leaves as $leave) {
            $department = Department::find($leave->department_id);
            if (!$department)
                continue;

            $total = $departmentStaffCounts[$leave->department_id] ?? 0;
            if ($total == 0)
                continue;

            $result[] = [
                'date' => $leave->leave_date->toDateString(),
                'department' => $department->name,
                'department_id' => $department->id,
                'count' => (int)$leave->count,
                'total' => (int)$total
            ];
        }

        return $result;
    }

    public function getDepartmentLeaveDetails($date, $departmentId)
    {
        return EmployeLeaveEntity::with(['employee', 'leaveType'])
            ->whereDate('leave_date', $date)
            ->where('department_id', $departmentId)
            ->where('status', 1) // Only show Used/Taken leaves
            ->get()
            ->map(function ($entity) {
            // Fetch quota for this specific employee and leave type
            $quota = EmployeeLeaveQuota::where('employee_id', $entity->employee_id)
                ->where('leave_type_id', $entity->leave_type_id)
                ->where('year', \Carbon\Carbon::parse($entity->leave_date)->year)
                ->first();

            $employee = $entity->employee;
            if (!$employee) {
                return [
                    'name' => 'Unknown Employee',
                    'id' => 'N/A',
                    'initials' => '??',
                    'leaveType' => $entity->leaveType->name ?? 'Leave',
                    'quota_info' => null
                ];
            }

            $nameParts = explode(' ', $employee->name);
            $initials = (isset($nameParts[0]) ? substr($nameParts[0], 0, 1) : '') .
                (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : '');

            return [
                'name' => $employee->name,
                'id' => $employee->employee_code ?? 'N/A',
                'initials' => strtoupper($initials),
                'leaveType' => $entity->leaveType->name ?? 'Leave',
                'quota_info' => $quota ? "{$quota->used}/{$quota->adjusted_quota}" : '0/0'
            ];
        });
    }

    public function store(array $data)
    {
        DB::beginTransaction();
        try {
            $holiday = PublicHoliday::create([
                'name' => $data['name'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'is_recurring' => (bool)($data['is_recurring'] ?? false),
                'organization_scope' => $data['organization_scope'] ?? 'all',
                'is_blackout' => (bool)($data['is_blackout'] ?? false),
                'reason' => $data['reason'] ?? null,
            ]);

            if ($holiday->organization_scope === 'specific' && !empty($data['organizations'])) {
                $holiday->organizations()->attach($data['organizations']);
            }

            DB::commit();
            return $holiday;
        }
        catch (\Exception $e) {
            DB::rollBack();
            Log::error('Holiday Store Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update(array $data, $id)
    {
        DB::beginTransaction();
        try {
            $holiday = PublicHoliday::findOrFail($id);
            $holiday->update([
                'name' => $data['name'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'is_recurring' => (bool)($data['is_recurring'] ?? false),
                'organization_scope' => $data['organization_scope'] ?? 'all',
                'is_blackout' => (bool)($data['is_blackout'] ?? false),
                'reason' => $data['reason'] ?? null,
            ]);

            if ($holiday->organization_scope === 'specific' && !empty($data['organizations'])) {
                $holiday->organizations()->sync($data['organizations']);
            }
            else {
                $holiday->organizations()->detach();
            }

            DB::commit();
            return $holiday;
        }
        catch (\Exception $e) {
            DB::rollBack();
            Log::error('Holiday Update Error: ' . $e->getMessage());
            throw $e;
        }
    }
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $holiday = PublicHoliday::findOrFail($id);
            $holiday->organizations()->detach();
            $holiday->delete();
            DB::commit();
            return true;
        }
        catch (\Exception $e) {
            DB::rollBack();
            Log::error('Holiday Deletion Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
