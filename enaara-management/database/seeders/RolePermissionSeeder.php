<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Permissions with human-readable labels.
     */
    protected function permissionsWithLabels(): array
    {
        return [
            'dashboard.view' => 'View Dashboard',

            'users.view' => 'View Users',
            'users.create' => 'Create Users',
            'users.edit' => 'Edit Users',
            'users.delete' => 'Delete Users',

            'employees.view' => 'View Employees',
            'employees.create' => 'Create Employees',
            'employees.edit' => 'Edit Employees',
            'employees.delete' => 'Delete Employees',

            'departments.view' => 'View Departments',
            'departments.create' => 'Create Departments',
            'departments.edit' => 'Edit Departments',
            'departments.delete' => 'Delete Departments',

            'organization.view' => 'View Organization',
            'organization.create' => 'Create Organization',
            'organization.edit' => 'Edit Organization',
            'organization.delete' => 'Delete Organization',

            'daily-logs.view' => 'View Daily Logs',
            'daily-logs.create' => 'Create Daily Logs',
            'daily-logs.edit' => 'Edit Daily Logs',
            'daily-logs.delete' => 'Delete Daily Logs',

            'shift-planner.view' => 'View Shift Planner',
            'shift-planner.create' => 'Create Shifts',
            'shift-planner.edit' => 'Edit Shifts',
            'shift-planner.delete' => 'Delete Shifts',

            'regularization.view' => 'View Regularization',
            'regularization.approve' => 'Approve Regularization',
            'regularization.reject' => 'Reject Regularization',

            'geofencing.view' => 'View Geofencing',
            'geofencing.create' => 'Create Geofences',
            'geofencing.edit' => 'Edit Geofences',
            'geofencing.delete' => 'Delete Geofences',

            'leave-requests.view' => 'View Leave Requests',
            'leave-requests.create' => 'Create Leave Requests',
            'leave-requests.approve' => 'Approve Leave Requests',
            'leave-requests.reject' => 'Reject Leave Requests',

            'my-leaves.view' => 'View My Leaves',
            'my-leaves.create' => 'Create My Leave Requests',

            'leave-calendar.view' => 'View Leave Calendar',
            'leave-calendar.manage' => 'Manage Leave Calendar',

            'balance-tracker.view' => 'View Balance Tracker',
            'balance-tracker.manage' => 'Manage Balance Tracker',

            'roles.view' => 'View Roles & Permissions',
            'roles.create' => 'Create Roles',
            'roles.edit' => 'Edit Roles',
            'roles.delete' => 'Delete Roles',
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'web';

        foreach ($this->permissionsWithLabels() as $name => $label) {
            $permission = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => $guard]
            );
            $permission->label = $label;
            $permission->save();
        }

        // Create roles and assign permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => $guard]);
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
        $admin->givePermissionTo(Permission::whereNotIn('name', ['roles.view', 'roles.create', 'roles.edit', 'roles.delete'])->get());

        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => $guard]);
        $managerPermissions = [
            'dashboard.view',
            'employees.view', 'employees.edit',
            'departments.view',
            'daily-logs.view', 'daily-logs.create', 'daily-logs.edit',
            'shift-planner.view', 'shift-planner.create', 'shift-planner.edit',
            'regularization.view', 'regularization.approve', 'regularization.reject',
            'geofencing.view',
            'leave-requests.view', 'leave-requests.approve', 'leave-requests.reject',
            'my-leaves.view', 'my-leaves.create',
            'leave-calendar.view',
            'balance-tracker.view',
        ];
        $manager->syncPermissions($managerPermissions);

        $employee = Role::firstOrCreate(['name' => 'employee', 'guard_name' => $guard]);
        $employeePermissions = [
            'dashboard.view',
            'my-leaves.view', 'my-leaves.create',
            'leave-calendar.view',
            'balance-tracker.view',
        ];
        $employee->syncPermissions($employeePermissions);
    }
}
