<?php

use App\Models\Organization;
use App\Models\Department;
use App\Models\Role;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

$org = Organization::first() ?? Organization::create(['name' => 'Test Org']);
$dept = Department::where('organization_id', $org->id)->first() ?? Department::create(['name' => 'IT Department', 'organization_id' => $org->id]);

// Create Roles
$managerRole = Role::updateOrCreate(
    ['name' => 'Manager HR', 'organization_id' => $org->id, 'department_id' => $dept->id],
    ['slug' => 'manager-hr', 'is_active' => true]
);

$tlRole = Role::updateOrCreate(
    ['name' => 'Teams Leader', 'organization_id' => $org->id, 'department_id' => $dept->id],
    ['slug' => 'teams-leader', 'parent_role_id' => $managerRole->id, 'is_active' => true]
);

$devRole = Role::updateOrCreate(
    ['name' => 'Developer', 'organization_id' => $org->id, 'department_id' => $dept->id],
    ['slug' => 'developer', 'parent_role_id' => $tlRole->id, 'is_active' => true]
);

echo "Roles setup complete.\n";

function createTestUserAndEmployee($name, $email, $roleId, $orgId, $deptId) {
    $user = User::updateOrCreate(
        ['email' => $email],
        [
            'name' => $name,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]
    );

    $employee = Employee::updateOrCreate(
        ['name' => $name, 'role_id' => $roleId],
        [
            'organization_id' => $orgId,
            'department_id' => $deptId,
            'is_active' => true,
        ]
    );

    $user->employee_id = $employee->id;
    $user->save();

    echo "Created Employee: $name with Role ID: $roleId\n";
    return $employee;
}

$managerEmp = createTestUserAndEmployee('Test Manager', 'manager@test.com', $managerRole->id, $org->id, $dept->id);
$tlEmp = createTestUserAndEmployee('Test Team Lead', 'tl@test.com', $tlRole->id, $org->id, $dept->id);
$devEmp = createTestUserAndEmployee('Test Developer', 'dev@test.com', $devRole->id, $org->id, $dept->id);

echo "Test Data Creation Finished.\n";
