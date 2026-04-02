<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\Department;
use App\Models\Role;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Str;

class LeaveHierarchySeeder extends Seeder
{
    public function run()
    {
        $org = Organization::firstOrCreate(
            ['name' => 'Enaara'],
            ['slug' => 'enaara-test-org', 'is_active' => true]
        );

        $sbu = \App\Models\Sbu::firstOrCreate(
            ['name' => 'Main SBU', 'organization_id' => $org->id],
            ['slug' => 'main-sbu-test', 'is_active' => true]
        );

        $dept = Department::firstOrCreate(
            ['name' => 'HR Department', 'organization_id' => $org->id],
            ['slug' => 'hr-dept-test', 'sbu_id' => $sbu->id, 'is_active' => true]
        );

        $ceoRole = Role::firstOrCreate(
            ['name' => 'CEO', 'organization_id' => $org->id],
            ['slug' => 'ceo-test', 'parent_role_id' => null, 'department_id' => null, 'is_active' => true]
        );

        $gmRole = Role::firstOrCreate(
            ['name' => 'GMHR', 'organization_id' => $org->id],
            ['slug' => 'gmhr-test', 'parent_role_id' => $ceoRole->id, 'department_id' => $dept->id, 'is_active' => true]
        );

        $hrRole = Role::firstOrCreate(
            ['name' => 'HR Manager', 'organization_id' => $org->id],
            ['slug' => 'hr-manager-test', 'parent_role_id' => $gmRole->id, 'department_id' => $dept->id, 'is_active' => true]
        );

        $tlRole = Role::firstOrCreate(
            ['name' => 'Team Leader', 'organization_id' => $org->id],
            ['slug' => 'team-leader-test', 'parent_role_id' => $gmRole->id, 'department_id' => $dept->id, 'is_active' => true]
        );

        $staffRole = Role::firstOrCreate(
            ['name' => 'Staff', 'organization_id' => $org->id],
            ['slug' => 'staff-test', 'parent_role_id' => $tlRole->id, 'department_id' => $dept->id, 'is_active' => true]
        );

        $this->createEmployeeUser('CEO User', 'ceo@enaara.com', $ceoRole, null);
        $this->createEmployeeUser('GMHR User', 'gm@enaara.com', $gmRole, $dept->id);
        $this->createEmployeeUser('HR Manager User', 'hr@enaara.com', $hrRole, $dept->id);
        $this->createEmployeeUser('Team Leader User', 'tl@enaara.com', $tlRole, $dept->id);
        $this->createEmployeeUser('Suleman', 'suleman@enaara.com', $staffRole, $dept->id);
        
        $this->command->info('Hierarchy seeding completed!');
        $this->command->info('Emails: ceo@enaara.com, gm@enaara.com, hr@enaara.com, tl@enaara.com, suleman@enaara.com');
        $this->command->info('Password for all users: password');
    }

    private function createEmployeeUser($name, $email, $role, $deptId)
    {
        $employee = Employee::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'role_id' => $role->id,
                'organization_id' => $role->organization_id,
                'department_id' => $deptId,
                'is_active' => true,
            ]
        );

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'employee_id' => $employee->id,
            ]
        );

        // Ensure user is linked to the role in user_roles table
        DB::table('user_roles')->updateOrInsert(
            ['user_id' => $user->id, 'role_id' => $role->id],
            ['created_at' => now(), 'updated_at' => now()]
        );

        return $user;
    }
}
