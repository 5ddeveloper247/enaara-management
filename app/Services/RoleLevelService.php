<?php

namespace App\Services;

use App\Models\RoleLevel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class RoleLevelService
{
    private function normalizeRoleLevelName(string $value): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim($value)));
    }

    private function ensureUniqueName(string $name, ?int $ignoreId = null): void
    {
        $normalized = $this->normalizeRoleLevelName($name);

        $query = RoleLevel::query();
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $exists = $query->get(['id', 'name'])->contains(function (RoleLevel $item) use ($normalized) {
            return $this->normalizeRoleLevelName((string) $item->name) === $normalized;
        });

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => ['This role level name is already in use.'],
            ]);
        }
    }

    public function getList(): Collection
    {
        return RoleLevel::excludingSystemAdmin()
            ->orderBy('level')
            ->orderBy('id')
            ->get();
    }

    public function getCounts(): array
    {
        $total = RoleLevel::excludingSystemAdmin()->count();
        $active = RoleLevel::excludingSystemAdmin()->where('is_active', true)->count();
        $inactive = RoleLevel::excludingSystemAdmin()->where('is_active', false)->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'active_percentage' => $total > 0 ? (int) round(($active / $total) * 100) : 0,
        ];
    }

    public function findById(int $id): ?RoleLevel
    {
        return RoleLevel::find($id);
    }

    public function create(array $data): RoleLevel
    {
        if (! empty($data['name'])) {
            $this->ensureUniqueName((string) $data['name']);
        }

        return RoleLevel::create($data);
    }

    public function update(RoleLevel $roleLevel, array $data): RoleLevel
    {
        if (! empty($data['name'])) {
            $this->ensureUniqueName((string) $data['name'], (int) $roleLevel->id);
        }

        $roleLevel->update($data);
        return $roleLevel->fresh();
    }

    public function destroy(RoleLevel $roleLevel): bool
    {
        return $roleLevel->delete();
    }
}
