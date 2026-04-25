<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Sbu;
use App\Models\SbuFloor;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MonthlySummaryService
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $sbuId = $request->get('sbu_id');
        $departmentId = $request->get('department_id');
        $floorId = $request->get('floor_id');

        $year = Carbon::parse($month . '-01')->year;

        $sbus = Sbu::query()->orderBy('name')->get();
        $departments = Department::query()->orderBy('name')->get();

        $floorsForFilter = SbuFloor::query()
            ->where('is_active', true)
            ->orderBy('sbu_id')
            ->orderBy('floor_number')
            ->orderBy('name')
            ->get(['id', 'sbu_id', 'name', 'floor_number'])
            ->map(fn (SbuFloor $f) => [
                'id' => $f->id,
                'sbu_id' => $f->sbu_id,
                'name' => $f->name,
                'floor_number' => $f->floor_number,
            ])
            ->values()
            ->all();

        $employeesQuery = Employee::with([
            'sbu',
            'department',
            'assignedFloors' => static fn ($q) => $q->where('is_active', true)->orderBy('floor_number'),
            'leaveQuotas' => function ($query) use ($year) {
                $query->where('year', $year)
                    ->with('leaveType');
            },
        ]);

        if (! empty($sbuId)) {
            $employeesQuery->where('sbu_id', $sbuId);
        }

        if (! empty($departmentId)) {
            $employeesQuery->where('department_id', $departmentId);
        }

        if ($floorId !== null && $floorId !== '') {
            $fid = (int) $floorId;
            if ($fid > 0) {
                $employeesQuery->whereHas('assignedFloors', static fn ($q) => $q->where('sbu_floors.id', $fid));
            }
        }

        $employees = $employeesQuery->get();

        $monthlySummary = $employees->map(function ($employee) use ($month) {
            return $this->buildEmployeeMonthlySummary($employee, $month);
        })->values();

        return view('admin.monthly-summary.index', compact(
            'monthlySummary',
            'sbus',
            'departments',
            'month',
            'floorsForFilter'
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

        $floors = $employee->relationLoaded('assignedFloors') ? $employee->assignedFloors : collect();
        $floorNames = $floors->pluck('name')->filter()->implode(', ');
        $sbuFloorIds = $floors->pluck('id')->map(static fn ($id) => (string) $id)->values()->all();
        $firstFloor = $floors->first();

        return [
            'employee_id' => $employee->id,
            'employee_code' => $employee->employee_code ?? 'N/A',
            'employee_name' => $employee->full_name ?? 'N/A',
            'employee_avatar' => strtoupper(substr($employee->full_name ?? 'E', 0, 1)),
            'department' => $employee->department->name ?? 'N/A',
            'department_id' => $employee->department_id,
            'sbu' => $employee->sbu->name ?? 'N/A',
            'sbu_id' => $employee->sbu_id,
            'floor_name' => $floorNames !== '' ? $floorNames : 'N/A',
            'sbu_floor_ids' => $sbuFloorIds,
            'floor_number' => $firstFloor?->floor_number,

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
