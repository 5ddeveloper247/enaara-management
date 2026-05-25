<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\Log;

class UserRoleSyncService
{
    public function syncFromEmployee(Employee $employee): void
    {
        $roleId = (int) ($employee->role_id ?? 0);
        if ($roleId <= 0) {
            return;
        }

        $user = User::query()->where('employee_id', $employee->id)->first();
        if (! $user) {
            return;
        }

        $activeRoles = $user->userRoles()->whereNull('deleted_at')->get();
        if ($activeRoles->count() === 1 && (int) $activeRoles->first()->role_id === $roleId) {
            return;
        }

        $user->userRoles()->whereNull('deleted_at')->update(['deleted_at' => now()]);
        UserRole::create([
            'user_id' => $user->id,
            'role_id' => $roleId,
        ]);

        Log::info('User roles synced from employee role', [
            'user_id' => $user->id,
            'employee_id' => $employee->id,
            'role_id' => $roleId,
        ]);
    }
}
