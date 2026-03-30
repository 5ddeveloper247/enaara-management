<?php

namespace App\Services;

use App\Models\ModuleCategory;
use App\Models\Role;
use App\Models\RolePrivilege;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class RoleService
{
    public function getList(): Collection
    {
        return Role::withCount('modules')
            ->with([
                'modules' => fn ($q) => $q->orderBy('module_name')->limit(5),
                'organization:id,name',
                'department:id,name',
                'parentRole:id,name',
            ])
            ->orderByDesc('id')
            ->get();
    }

    public function getCounts(): array
    {
        return [
            'total' => Role::count(),
            'active' => Role::where('is_active', true)->count(),
            'inactive' => Role::where('is_active', false)->count(),
        ];
    }

    public function findById(int $id): ?Role
    {
        return Role::with([
            'modules',
            'organization:id,name',
            'department:id,name',
            'parentRole:id,name',
        ])->find($id);
    }

    public function getModuleCategoriesWithModules(): Collection
    {
        return ModuleCategory::with([
            'modules' => fn ($q) => $q->orderBy('display_order'),
        ])
            ->orderBy('display_order')
            ->orderBy('category_name')
            ->get();
    }

    public function create(array $data): Role
    {
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Role::slugFromName($data['name']);
        }

        $moduleIds = $data['module_ids'] ?? [];
        unset($data['module_ids']);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['is_primary'] = (bool) ($data['is_primary'] ?? false);
        $data['organization_id'] = $data['organization_id'] ?? null;
        $data['department_id'] = $data['department_id'] ?? null;
        $data['parent_role_id'] = $data['parent_role_id'] ?? null;

        $role = Role::create($data);

        $this->syncRolePrivileges($role->id, $moduleIds);

        return $role->fresh([
            'modules',
            'organization:id,name',
            'department:id,name',
            'parentRole:id,name',
        ]);
    }

    public function update(Role $role, array $data): Role
    {
        if (array_key_exists('name', $data) && (empty($data['slug']) || $data['slug'] === $role->slug)) {
            $data['slug'] = Role::slugFromName($data['name']);
        }

        $moduleIds = $data['module_ids'] ?? null;
        unset($data['module_ids']);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['is_primary'] = (bool) ($data['is_primary'] ?? false);
        $data['organization_id'] = $data['organization_id'] ?? null;
        $data['department_id'] = $data['department_id'] ?? null;
        $data['parent_role_id'] = $data['parent_role_id'] ?? null;

        if (!empty($data['parent_role_id']) && (int) $data['parent_role_id'] === (int) $role->id) {
            throw ValidationException::withMessages([
                'parent_role_id' => 'A role cannot be its own parent.',
            ]);
        }

        $role->update($data);

        if ($moduleIds !== null) {
            $this->syncRolePrivileges($role->id, $moduleIds);
        }

        return $role->fresh([
            'modules',
            'organization:id,name',
            'department:id,name',
            'parentRole:id,name',
        ]);
    }

    protected function syncRolePrivileges(int $roleId, array $moduleIds): void
    {
        RolePrivilege::where('role_id', $roleId)->delete();

        $moduleIds = array_filter(array_unique(array_map('intval', $moduleIds)));

        foreach ($moduleIds as $moduleId) {
            RolePrivilege::create([
                'role_id' => $roleId,
                'module_id' => $moduleId,
            ]);
        }
    }

    public function updateStatus(int $id, bool $isActive): ?Role
    {
        $role = Role::find($id);

        if (!$role) {
            return null;
        }

        $role->is_active = $isActive;
        $role->save();

        return $role;
    }

    public function delete(int $id): bool
    {
        $role = Role::find($id);

        if (!$role) {
            return false;
        }

        RolePrivilege::where('role_id', $role->id)->delete();
        $role->delete();

        return true;
    }

    public function searchRole(string $term): Collection
    {
        return Role::query()
            ->when($term !== '', function ($q) use ($term) {
                $q->where(function ($qq) use ($term) {
                    $qq->where('name', 'like', '%' . $term . '%')
                        ->orWhere('slug', 'like', '%' . $term . '%');
                });
            })
            ->orderByDesc('id')
            ->limit(20)
            ->get(['id', 'name', 'slug']);
    }
}