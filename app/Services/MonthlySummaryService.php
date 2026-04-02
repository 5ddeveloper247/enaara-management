<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Sbu;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MonthlySummaryService
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $sbuId = $request->get('sbu_id');
        $departmentId = $request->get('department_id');

        $year = Carbon::parse($month . '-01')->year;

        $sbus = Sbu::all();
        $departments = Department::all();

        $employeesQuery = Employee::with([
            'sbu',
            'department',
            'leaveQuotas' => function ($query) use ($year) {
                $query->where('year', $year)
                    ->with('leaveType');
            }
        ]);

        if (!empty($sbuId)) {
            $employeesQuery->where('sbu_id', $sbuId);
        }

        if (!empty($departmentId)) {
            $employeesQuery->where('department_id', $departmentId);
        }

        $employees = $employeesQuery->get();

        $monthlySummary = $employees->map(function ($employee) use ($month) {
            return $this->buildEmployeeMonthlySummary($employee, $month);
        })->values();

        return view('admin.monthly-summary.index', compact(
            'monthlySummary',
            'sbus',
            'departments',
            'month'
        ));
    }

    private function buildEmployeeMonthlySummary(Employee $employee, string $month): array
    {
        $startDate = Carbon::parse($month . '-01')->startOfMonth();

        $annualLeaveUsed = 0;
        $sickLeaveUsed = 0;
        $casualLeaveUsed = 0;

        foreach ($employee->leaveQuotas as $quota) {
            $leaveTypeName = strtolower(trim($quota->leaveType->name ?? ''));

            if (str_contains($leaveTypeName, 'annual')) {
                $annualLeaveUsed = (float) $quota->used;
            } elseif (str_contains($leaveTypeName, 'sick')) {
                $sickLeaveUsed = (float) $quota->used;
            } elseif (str_contains($leaveTypeName, 'casual')) {
                $casualLeaveUsed = (float) $quota->used;
            }
        }

        return [
            'employee_id' => $employee->id,
            'employee_code' => $employee->employee_code ?? 'N/A',
            'employee_name' => $employee->full_name ?? 'N/A',
            'employee_avatar' => strtoupper(substr($employee->full_name ?? 'E', 0, 1)),
            'department' => $employee->department->name ?? 'N/A',
            'department_id' => $employee->department_id,
            'sbu' => $employee->sbu->name ?? 'N/A',
            'sbu_id' => $employee->sbu_id,

            'total_days' => $startDate->daysInMonth,
            'present' => 0,
            'absent' => 0,
            'half_days' => 0,

            'annual_leave' => $annualLeaveUsed,
            'sick_leave' => $sickLeaveUsed,
            'casual_leave' => $casualLeaveUsed,

            'late_arrivals' => 0,
            'early_departures' => 0,
            'zone2_verification' => 'N/A',
            'regularization' => 0,
        ];
    }
}