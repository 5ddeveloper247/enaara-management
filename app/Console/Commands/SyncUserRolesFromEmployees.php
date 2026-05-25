<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\User;
use App\Services\UserRoleSyncService;
use Illuminate\Console\Command;

class SyncUserRolesFromEmployees extends Command
{
    protected $signature = 'employees:sync-user-roles';

    protected $description = 'Sync user_roles from employees.role_id for all linked users';

    public function handle(UserRoleSyncService $userRoleSyncService): int
    {
        $employeeIds = User::query()
            ->whereNotNull('employee_id')
            ->pluck('employee_id')
            ->unique()
            ->all();

        $synced = 0;
        foreach (Employee::query()->whereIn('id', $employeeIds)->whereNotNull('role_id')->cursor() as $employee) {
            $userRoleSyncService->syncFromEmployee($employee);
            $synced++;
        }

        $this->info("Processed {$synced} employee(s) linked to user accounts.");

        return self::SUCCESS;
    }
}
