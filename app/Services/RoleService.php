<?php

namespace App\Services;

use App\Models\ModuleCategory;
use App\Models\Role;
use App\Models\RolePrivilege;
use Illuminate\Database\Eloquent\Collection;

class RoleService
{
    public function getList(): Collection
    {
        return Role::withCount('modules')
            ->with(['modules' => fn ($q) => $q->orderBy('module_name')->limit(5)])
            ->orderByDesc('id')
            ->get();
    }

    public function getCounts(): array
    {
        $total = Role::count();
        $active = Role::where('is_active', true)->count();
        $inactive = Role::where('is_active', false)->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
        ];
    }

    public function findById(int $id): ?Role
    {
        return Role::with('modules')->find($id);
    }

    public function getModuleCategoriesWithModules(): Collection
    {
        return ModuleCategory::with(['modules' => fn ($q) => $q->orderBy('display_order')])
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
        $role = Role::create($data);
        $this->syncRolePrivileges($role->id, $moduleIds);
        return $role->fresh(['modules']);
    }

    public function update(Role $role, array $data): Role
    {
        if (array_key_exists('name', $data) && (empty($data['slug']) || $data['slug'] === $role->slug)) {
            $data['slug'] = Role::slugFromName($data['name']);
        }
        $moduleIds = $data['module_ids'] ?? null;
        unset($data['module_ids']);
        $role->update($data);
        if ($moduleIds !== null) {
            $this->syncRolePrivileges($role->id, $moduleIds);
        }
        return $role->fresh(['modules']);
    }

    protected function syncRolePrivileges(int $roleId, array $moduleIds): void
    {
        RolePrivilege::where('role_id', $roleId)->delete();
        $moduleIds = array_filter(array_unique($moduleIds));
        foreach ($moduleIds as $moduleId) {
            RolePrivilege::create([
                'role_id' => $roleId,
                'module_id' => (int) $moduleId,
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
        $role->delete();
        return true;
    }

    public function searchRole(string $term): Collection
    {
        return Role::where('name', 'like', '%' . $term . '%')
            ->orWhere('slug', 'like', '%' . $term . '%')
            ->orderByDesc('id')
            ->limit(20)
            ->get(['id', 'name', 'slug']);
    }
}
