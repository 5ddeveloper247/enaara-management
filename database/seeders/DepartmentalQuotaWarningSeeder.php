<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\EmployeLeaveRequest;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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

        $departments = Department::query()->where('is_active', true)->get(['id', 'organization_id']);

        if ($departments->isEmpty()) {
            $this->command?->warn('DepartmentalQuotaWarningSeeder: No active departments found.');
            return;
        }

        foreach ($departments as $department) {
            $activeEmployees = Employee::query()
                ->where('department_id', $department->id)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->get(['id', 'department_id']);

            // Guarantee enough active employees in department so warning is visible.
            $missing = max(0, 3 - $activeEmployees->count());

            for ($i = 1; $i <= $missing; $i++) {
                $seedEmployee = Employee::query()->create([
                    'full_name' => 'Quota Seed Emp ' . $department->id . '-' . $i,
                    'organization_id' => $department->organization_id,
                    'department_id' => $department->id,
                    'employee_code' => 'QSEED-' . $department->id . '-' . Str::upper(Str::random(5)),
                    'email' => 'qseed-' . $department->id . '-' . Str::lower(Str::random(6)) . '@example.com',
                    'is_active' => true,
                ]);

                $activeEmployees->push($seedEmployee);
            }

            $targetDate = Carbon::today()->addDays(3)->toDateString();
            $total = $activeEmployees->count();
            $requiredOnLeave = max(1, (int) ceil($total * 0.35));
            $selectedEmployees = $activeEmployees->take($requiredOnLeave);

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
                        'department_id' => $department->id,
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
