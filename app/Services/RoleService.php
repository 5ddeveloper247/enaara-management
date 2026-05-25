<?php

namespace App\Services;

use App\Models\Department;
use App\Models\ModuleCategory;
use App\Models\Role;
use App\Models\RolePrivilege;
use App\Models\Sbu;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class RoleService
{
    protected function normalizeRoleName(string $name): string
    {
        return mb_strtolower(trim($name));
    }

    protected function collectRoleSbuIds(Role $role): array
    {
        $ids = $role->sbus->pluck('id')->map(fn ($id) => (int) $id)->all();
        if (!empty($role->sbu_id)) {
            $ids[] = (int) $role->sbu_id;
        }
        return array_values(array_unique(array_filter($ids)));
    }

    protected function validateRoleNameUniquenessByScope(array $data, ?int $currentRoleId = null): void
    {
        $organizationId = (int) ($data['organization_id'] ?? 0);
        $name = trim((string) ($data['name'] ?? ''));
        $targetSbuIds = array_values(array_filter(array_unique(array_map('intval', $data['sbu_ids'] ?? []))));
        if (empty($targetSbuIds) && !empty($data['sbu_id'])) {
            $targetSbuIds = [(int) $data['sbu_id']];
        }

        if ($organizationId <= 0 || $name === '') {
            return;
        }

        $existingRoles = Role::query()
            ->with('sbus:id')
            ->where('organization_id', $organizationId)
            ->whereRaw('LOWER(TRIM(name)) = ?', [$this->normalizeRoleName($name)])
            ->when($currentRoleId, fn ($q) => $q->where('id', '!=', $currentRoleId))
            ->get();

        foreach ($existingRoles as $existingRole) {
            $existingSbuIds = $this->collectRoleSbuIds($existingRole);

            if (empty($targetSbuIds)) {
                if (empty($existingSbuIds)) {
                    throw ValidationException::withMessages([
                        'level_id' => 'This role level already exists for the selected organization.',
                    ]);
                }
                continue;
            }

            $overlap = array_intersect($targetSbuIds, $existingSbuIds);
            if (!empty($overlap)) {
                throw ValidationException::withMessages([
                    'level_id' => 'This role level already exists in one or more selected SBUs for this organization.',
                ]);
            }
        }
    }

    public function getList(): Collection
    {
        return Role::excludingSystemAdmin()
            ->withCount('modules')
            ->with([
                'modules' => fn ($q) => $q->orderBy('module_name')->limit(5),
                'organization:id,name',
                'sbu:id,name',
                'sbus:id,name',
                'department:id,name',
                'parentRole:id,name',
            ])
            ->orderByDesc('id')
            ->get();
    }

    public function getCounts(): array
    {
        return [
            'total' => Role::excludingSystemAdmin()->count(),
            'active' => Role::excludingSystemAdmin()->where('is_active', true)->count(),
            'inactive' => Role::excludingSystemAdmin()->where('is_active', false)->count(),
        ];
    }

    public function findById(int $id): ?Role
    {
        return Role::with([
            'modules',
            'organization:id,name',
            'sbu:id,name',
            'sbus:id,name',
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
            $data['slug'] = $this->buildUniqueSlug(Role::slugFromName($data['name']));
        }

        $moduleIds = $data['module_ids'] ?? [];
        $sbuIds = $data['sbu_ids'] ?? (!empty($data['sbu_id']) ? [$data['sbu_id']] : []);
        unset($data['module_ids']);
        unset($data['sbu_ids']);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['is_primary'] = (bool) ($data['is_primary'] ?? false);
        $data['is_system_admin'] = (bool) ($data['is_system_admin'] ?? false);
        $data['organization_id'] = $data['organization_id'] ?? null;
        $sbuIds = array_values(array_filter(array_unique(array_map('intval', $sbuIds))));
        $data['sbu_id'] = $sbuIds[0] ?? null;
        $data['department_id'] = $data['department_id'] ?? null;
        $data['parent_role_id'] = $data['parent_role_id'] ?? null;

        $this->validateScopeData($data);
        $this->validateRoleNameUniquenessByScope($data);

        $role = Role::create($data);

        $this->syncRolePrivileges($role->id, $moduleIds);
        $this->syncRoleSbus($role, $sbuIds);

        return $role->fresh([
            'modules',
            'organization:id,name',
            'sbu:id,name',
            'sbus:id,name',
            'department:id,name',
            'parentRole:id,name',
        ]);
    }

    public function update(Role $role, array $data): Role
    {
        if (array_key_exists('name', $data) && (empty($data['slug']) || $data['slug'] === $role->slug)) {
            $data['slug'] = $this->buildUniqueSlug(Role::slugFromName($data['name']), $role->id);
        }

        $moduleIds = $data['module_ids'] ?? null;
        $sbuIds = $data['sbu_ids'] ?? (array_key_exists('sbu_id', $data) ? [$data['sbu_id']] : null);
        unset($data['module_ids']);
        unset($data['sbu_ids']);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['is_primary'] = (bool) ($data['is_primary'] ?? false);
        $data['is_system_admin'] = (bool) ($data['is_system_admin'] ?? false);
        $data['organization_id'] = $data['organization_id'] ?? null;
        if ($sbuIds !== null) {
            $sbuIds = array_values(array_filter(array_unique(array_map('intval', $sbuIds))));
            $data['sbu_id'] = $sbuIds[0] ?? null;
        } else {
            $data['sbu_id'] = $data['sbu_id'] ?? null;
        }
        $data['department_id'] = $data['department_id'] ?? null;
        $data['parent_role_id'] = $data['parent_role_id'] ?? null;

        $this->validateScopeData($data, $role->id);
        $this->validateRoleNameUniquenessByScope($data, $role->id);

        $role->update($data);

        if ($moduleIds !== null) {
            $this->syncRolePrivileges($role->id, $moduleIds);
        }
        if ($sbuIds !== null) {
            $this->syncRoleSbus($role, $sbuIds);
        }

        return $role->fresh([
            'modules',
            'organization:id,name',
            'sbu:id,name',
            'sbus:id,name',
            'department:id,name',
            'parentRole:id,name',
        ]);
    }

    protected function syncRolePrivileges(int $roleId, array $moduleIds): void
    {
        RolePrivilege::withTrashed()->where('role_id', $roleId)->forceDelete();

        $moduleIds = array_filter(array_unique(array_map('intval', $moduleIds)));

        foreach ($moduleIds as $moduleId) {
            RolePrivilege::create([
                'role_id' => $roleId,
                'module_id' => $moduleId,
            ]);
        }
    }

    protected function validateScopeData(array $data, ?int $currentRoleId = null): void
    {
        $organizationId = (int) ($data['organization_id'] ?? 0);
        $sbuIds = array_values(array_filter(array_unique(array_map('intval', $data['sbu_ids'] ?? []))));
        $departmentId = isset($data['department_id']) && $data['department_id'] !== '' ? (int) $data['department_id'] : null;
        $parentRoleId = isset($data['parent_role_id']) && $data['parent_role_id'] !== '' ? (int) $data['parent_role_id'] : null;

        if ($organizationId <= 0) {
            throw ValidationException::withMessages([
                'organization_id' => 'Organization is required.',
            ]);
        }

        if (!empty($sbuIds)) {
            $validSbuCount = Sbu::query()
                ->where('organization_id', $organizationId)
                ->whereIn('id', $sbuIds)
                ->count();
            if ($validSbuCount !== count($sbuIds)) {
                throw ValidationException::withMessages([
                    'sbu_ids' => 'Selected SBU must belong to the selected organization.',
                ]);
            }
        }

        if ($departmentId !== null) {
            $department = Department::query()->find($departmentId);
            if (!$department) {
                throw ValidationException::withMessages([
                    'department_id' => 'Selected department is invalid.',
                ]);
            }
            if ((int) $department->organization_id !== $organizationId) {
                throw ValidationException::withMessages([
                    'department_id' => 'Selected department must belong to the selected organization.',
                ]);
            }
            if (empty($sbuIds)) {
                throw ValidationException::withMessages([
                    'sbu_ids' => 'Please select at least one SBU when department is selected.',
                ]);
            }
            if (!in_array((int) $department->sbu_id, $sbuIds, true)) {
                throw ValidationException::withMessages([
                    'department_id' => 'Selected department must belong to one of the selected SBUs.',
                ]);
            }
        }

        if ($parentRoleId !== null && $currentRoleId !== null) {
            if ($parentRoleId === $currentRoleId) {
                throw ValidationException::withMessages([
                    'parent_role_id' => 'A role cannot be its own parent.',
                ]);
            }
            if ($this->createsParentCycle($currentRoleId, $parentRoleId)) {
                throw ValidationException::withMessages([
                    'parent_role_id' => 'Selected parent role creates a cyclic hierarchy.',
                ]);
            }
        }
    }

    protected function createsParentCycle(int $currentRoleId, int $parentRoleId): bool
    {
        $visited = [];
        $cursor = $parentRoleId;

        while ($cursor) {
            if ($cursor === $currentRoleId) {
                return true;
            }
            if (in_array($cursor, $visited, true)) {
                return true;
            }
            $visited[] = $cursor;
            $cursor = (int) (Role::query()->whereKey($cursor)->value('parent_role_id') ?? 0);
        }

        return false;
    }

    protected function syncRoleSbus(Role $role, array $sbuIds): void
    {
        $sbuIds = array_values(array_filter(array_unique(array_map('intval', $sbuIds))));
        $role->sbus()->sync($sbuIds);
    }

    protected function buildUniqueSlug(string $baseSlug, ?int $ignoreRoleId = null): string
    {
        $baseSlug = trim($baseSlug) !== '' ? $baseSlug : 'role';
        $candidate = $baseSlug;
        $suffix = 2;

        while (
            Role::query()
                ->where('slug', $candidate)
                ->when($ignoreRoleId, fn ($q) => $q->where('id', '!=', $ignoreRoleId))
                ->exists()
        ) {
            $candidate = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
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

        RolePrivilege::withTrashed()->where('role_id', $role->id)->forceDelete();
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
