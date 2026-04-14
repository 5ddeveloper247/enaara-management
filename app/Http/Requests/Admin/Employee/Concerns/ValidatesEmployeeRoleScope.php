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
}
