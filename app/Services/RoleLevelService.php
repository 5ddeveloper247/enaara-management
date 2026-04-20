<?php

namespace App\Services;

use App\Models\RoleLevel;
use Illuminate\Database\Eloquent\Collection;

class RoleLevelService
{
    public function getList(): Collection
    {
        return RoleLevel::orderBy('level')->orderBy('id')->get();
    }

    public function getCounts(): array
    {
        $total = RoleLevel::count();
        $active = RoleLevel::where('is_active', true)->count();
        $inactive = RoleLevel::where('is_active', false)->count();

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
        return RoleLevel::create($data);
    }

    public function update(RoleLevel $roleLevel, array $data): RoleLevel
    {
        $roleLevel->update($data);
        return $roleLevel->fresh();
    }

    public function destroy(RoleLevel $roleLevel): bool
    {
        return $roleLevel->delete();
    }
}
