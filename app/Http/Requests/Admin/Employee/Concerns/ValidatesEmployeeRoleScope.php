<?php

namespace App\Http\Requests\Admin\Employee\Concerns;

use App\Models\Role;

trait ValidatesEmployeeRoleScope
{
    protected function orgLevelRoleSelected(): bool
    {
        $role = $this->resolveRoleForOrgLevelCheck();

        return $role !== null && $role->isOrganizationLevelRole();
    }

    protected function resolveRoleForOrgLevelCheck(): ?Role
    {
        $roleId = $this->input('role_id');
        if (! $roleId) {
            return null;
        }

        return Role::query()->find($roleId);
    }

    protected function deptRequiredForRole(): bool
    {
        $roleId = $this->input('role_id');
        if (! $roleId) {
            return false;
        }

        $role = Role::query()->find($roleId);
        if (! $role) {
            return false;
        }

        $level = $role->resolvedNumericLevel();

        return $level !== null && $level >= 4;
    }
}
