<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\Role;
use App\Models\Sbu;
use App\Models\User;
use App\Models\LeaveType;
use App\Models\EmployeeLeaveQuota;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Organization
        $org = Organization::updateOrCreate(
            ['code' => 'ENA-MS'],
            [
                'name' => 'Enaara Management System',
                'email' => 'admin@enaara-ms.com',
                'address' => 'Enaara HQ, Islamabad',
                'is_active' => true,
            ]
        );

        // 2. Create SBU
        $sbu = Sbu::updateOrCreate(
            ['organization_id' => $org->id, 'name' => 'Enaara HQ'],
            [
                'city' => 'Islamabad',
                'address' => 'Enaara HQ, Islamabad',
                'is_active' => true,
            ]
        );

        // 3. Create Departments
        $departments = collect(['Administration', 'Operations', 'Tech Support'])->map(function ($name) use ($org, $sbu) {
            return Department::updateOrCreate(
                ['organization_id' => $org->id, 'sbu_id' => $sbu->id, 'name' => $name],
                [
                    'code' => strtoupper(substr($name, 0, 3)),
                    'is_active' => true,
                ]
            );
        });

        // 3.5 Create Leave Types for the organization
        $leaveTypesData = [
            ['name' => 'Annual Leave', 'code' => 'AL', 'quota' => 30],
            ['name' => 'Sick Leave', 'code' => 'SL', 'quota' => 15],
            ['name' => 'Casual Leave', 'code' => 'CL', 'quota' => 10],
            ['name' => 'Maternity Leave', 'code' => 'ML', 'quota' => 90],
            ['name' => 'Paternity Leave', 'code' => 'PL', 'quota' => 10],
        ];

        foreach ($leaveTypesData as $ltData) {
            LeaveType::updateOrCreate(
                ['organization_id' => $org->id, 'code' => $ltData['code']],
                [
                    'name' => $ltData['name'],
                    'annual_quota' => $ltData['quota'],
                    'is_active' => true,
                ]
            );
        }

        $activeLeaveTypes = LeaveType::where('organization_id', $org->id)->where('is_active', true)->get();

        // 4. Create CEO Role (Org Level)
        $ceoRole = Role::updateOrCreate(
            ['slug' => 'ceo'],
            [
                'name' => 'CEO',
                'organization_id' => $org->id,
                'description' => 'Chief Executive Officer',
                'parent_role_id' => null,
                'department_id' => null,
                'is_active' => true,
            ]
        );

        $password = Hash::make('password123');
        $this->createEmployeesForRole($ceoRole, $org, $sbu, null, $password, 'ceo_org', 2, $activeLeaveTypes);

        // 5. Create Departmental Hierarchy
        foreach ($departments as $dept) {
            $deptCode = strtolower($dept->code);

            // GM Role
            $gmRole = Role::updateOrCreate(
                ['slug' => "gm-{$deptCode}", 'department_id' => $dept->id],
                [
                    'name' => "GM - {$dept->name}",
                    'organization_id' => $org->id,
                    'parent_role_id' => $ceoRole->id,
                    'is_active' => true,
                ]
            );
            $this->createEmployeesForRole($gmRole, $org, $sbu, $dept, $password, "gm_{$deptCode}", 2, $activeLeaveTypes);

            // HR Role
            $hrRole = Role::updateOrCreate(
                ['slug' => "hr-{$deptCode}", 'department_id' => $dept->id],
                [
                    'name' => "HR - {$dept->name}",
                    'organization_id' => $org->id,
                    'parent_role_id' => $gmRole->id,
                    'is_active' => true,
                ]
            );
            $this->createEmployeesForRole($hrRole, $org, $sbu, $dept, $password, "hr_{$deptCode}", 2, $activeLeaveTypes);

            // SM Role
            $smRole = Role::updateOrCreate(
                ['slug' => "sm-{$deptCode}", 'department_id' => $dept->id],
                [
                    'name' => "SM - {$dept->name}",
                    'organization_id' => $org->id,
                    'parent_role_id' => $hrRole->id,
                    'is_active' => true,
                ]
            );
            $this->createEmployeesForRole($smRole, $org, $sbu, $dept, $password, "sm_{$deptCode}", 2, $activeLeaveTypes);

            // Executive Role
            $execRole = Role::updateOrCreate(
                ['slug' => "exec-{$deptCode}", 'department_id' => $dept->id],
                [
                    'name' => "Executive - {$dept->name}",
                    'organization_id' => $org->id,
                    'parent_role_id' => $smRole->id,
                    'is_active' => true,
                ]
            );
            $this->createEmployeesForRole($execRole, $org, $sbu, $dept, $password, "exec_{$deptCode}", 2, $activeLeaveTypes);

            // Staff Role
            $staffRole = Role::updateOrCreate(
                ['slug' => "staff-{$deptCode}", 'department_id' => $dept->id],
                [
                    'name' => "Staff - {$dept->name}",
                    'organization_id' => $org->id,
                    'parent_role_id' => $execRole->id,
                    'is_active' => true,
                ]
            );
            $this->createEmployeesForRole($staffRole, $org, $sbu, $dept, $password, "staff_{$deptCode}", 2, $activeLeaveTypes);
        }
    }

    private function createEmployeesForRole($role, $org, $sbu, $dept, $password, $prefix, $count, $leaveTypes = null)
    {
        for ($i = 1; $i <= $count; $i++) {
            $username = "{$prefix}_{$i}";
            $email = "{$username}@enaara-ms.com";
            $fullName = "{$role->name} User {$i}";

            $employee = Employee::updateOrCreate(
                ['email' => $email],
                [
                    'full_name' => $fullName,
                    'employee_code' => strtoupper("EMP-{$role->slug}-" . Str::random(4)),
                    'organization_id' => $org->id,
                    'sbu_id' => $sbu->id,
                    'department_id' => $dept ? $dept->id : null,
                    'role_id' => $role->id,
                    'is_active' => true,
                    'join_date' => now(),
                ]
            );

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $fullName,
                    'username' => $username,
                    'password' => $password,
                    'employee_id' => $employee->id,
                    'is_active' => true,
                ]
            );

            $employee->update(['user_id' => $user->id]);

            // Initialize Leave Quota
            if ($leaveTypes) {
                foreach ($leaveTypes as $lt) {
                    EmployeeLeaveQuota::updateOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'leave_type_id' => $lt->id,
                            'year' => 2026,
                        ],
                        [
                            'department_id' => $employee->department_id,
                            'quota' => $lt->annual_quota,
                            'used' => 0,
                        ]
                    );
                }
            }

            // Assign role to user
            \App\Models\UserRole::updateOrCreate(
                ['user_id' => $user->id, 'role_id' => $role->id]
            );
        }
    }
}
