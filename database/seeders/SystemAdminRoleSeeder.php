<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\RoleLevel;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SystemAdminRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roleLevel = RoleLevel::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', ['super admin'])
            ->first();

        if (! $roleLevel) {
            $roleLevel = RoleLevel::create([
                'name' => 'Super Admin',
                'description' => null,
                'level' => 786,
                'grade' => 'G-SA',
                'is_active' => true,
            ]);
        } else {
            $roleLevel->update([
                'name' => 'Super Admin',
                'level' => 786,
                'is_active' => true,
            ]);
        }

        $role = Role::query()->firstOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Admin',
                'description' => null,
                'organization_id' => null,
                'role_level_id' => $roleLevel->id,
                'is_active' => true,
                'is_primary' => false,
                'is_system_admin' => true,
            ]
        );

        $role->update([
            'is_system_admin' => true,
            'role_level_id' => $roleLevel->id,
        ]);

        $email = 'super.admin@efmoffices.com';
        $password = 'SuperAdmin@123';

        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin',
                'password' => Hash::make($password),
                'employee_id' => null,
                'is_active' => true,
                'must_change_password' => false,
            ]
        );

        UserRole::query()
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now()]);

        UserRole::create([
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);

        $this->command?->info('Super Admin ready.');
        $this->command?->info('Role level priority: ' . $roleLevel->level);
        $this->command?->info('Email: ' . $email);
        $this->command?->info('Password: ' . $password);
    }
}
