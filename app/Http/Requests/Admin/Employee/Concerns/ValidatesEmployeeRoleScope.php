<?php

namespace App\Http\Requests\Admin\Employee\Concerns;

use App\Models\Role;
use Illuminate\Support\Facades\Log;

trait ValidatesEmployeeRoleScope
{
    protected function prepareForValidation(): void
    {
        $role = $this->resolveRoleForOrgLevelCheck();
        $orgLevel = $role && $role->isOrganizationLevelRole();
        Log::info(class_basename(static::class).' employment scope', [
            'role_id'              => $this->input('role_id'),
            'role_slug'            => $role?->slug,
            'role_name'            => $role?->name,
            'role_department_id'   => $role?->department_id,
            'org_level_role'       => $orgLevel,
        ]);
        if ($orgLevel) {
            $this->merge([
                'sbu_id'         => null,
                'department_id'  => null,
            ]);
        }
    }

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
