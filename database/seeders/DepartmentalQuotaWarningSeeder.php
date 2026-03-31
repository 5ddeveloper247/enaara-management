<?php

namespace Database\Seeders;

use App\Models\EmployeLeaveRequest;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DepartmentalQuotaWarningSeeder extends Seeder
{
    public function run(): void
    {
        $leaveTypeId = LeaveType::query()->where('is_active', true)->value('id');

        if (!$leaveTypeId) {
            $organizationId = Organization::query()->value('id');

            if (!$organizationId) {
                $this->command?->warn('DepartmentalQuotaWarningSeeder: No organization found to create a leave type.');
                return;
            }

            $leaveType = LeaveType::query()->create([
                'organization_id' => $organizationId,
                'name' => 'Seeded Annual Leave',
                'code' => 'SEED-ANNUAL',
                'annual_quota' => 20,
                'is_active' => true,
            ]);

            $leaveTypeId = $leaveType->id;
        }

        $targetDate = Carbon::today()->addDays(3)->toDateString();

        $employeesByDepartment = Employee::query()
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->whereNotNull('department_id')
            ->select('id', 'department_id')
            ->orderBy('id')
            ->get()
            ->groupBy('department_id');

        foreach ($employeesByDepartment as $departmentId => $employees) {
            $total = $employees->count();

            if ($total < 1) {
                continue;
            }

            // Make sure this department breaches the 20% warning threshold.
            $requiredOnLeave = max(1, (int) ceil($total * 0.35));
            $selectedEmployees = $employees->take($requiredOnLeave);

            foreach ($selectedEmployees as $employee) {
                EmployeLeaveRequest::query()->updateOrCreate(
                    [
                        'from_employee_id' => $employee->id,
                        'start_date' => $targetDate,
                        'end_date' => $targetDate,
                        'action_type' => 2,
                    ],
                    [
                        'to_employee_id' => null,
                        'from_user_id' => null,
                        'to_user_id' => null,
                        'department_id' => (int) $departmentId,
                        'leave_type_id' => $leaveTypeId,
                        'duration' => 1,
                        'reason' => 'Seeded departmental quota warning data',
                        'medical_report' => null,
                        'status' => 3,
                    ]
                );
            }
        }

        $this->command?->info('Departmental quota warning demo data seeded successfully.');
    }
}
