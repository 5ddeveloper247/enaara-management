<?php

namespace App\Services;

use App\Models\ModuleCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ModuleCategoryService
{
    public function getList(): Collection
    {
        return ModuleCategory::withCount('modules')
            ->with(['modules' => fn ($q) => $q->orderBy('display_order')->limit(5)])
            ->orderByDesc('ID')
            ->get();
    }

    public function getCounts(): array
    {
        $total = ModuleCategory::count();
        $active = ModuleCategory::where('is_active', true)->count();
        $inactive = ModuleCategory::where('is_active', false)->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
        ];
    }

    public function findById(int|string $id): ?ModuleCategory
    {
        return ModuleCategory::with('modules')->find($id);
    }

    public function create(array $data): ModuleCategory
    {
        return ModuleCategory::create($data);
    }

    public function update(ModuleCategory $moduleCategory, array $data): ModuleCategory
    {
        $moduleCategory->update($data);
        return $moduleCategory->fresh();
    }

    public function updateStatus(int|string $id, bool $isActive): ?ModuleCategory
    {
        $category = ModuleCategory::find($id);
        if (!$category) {
            return null;
        }
        $category->is_active = $isActive;
        $category->save();
        return $category;
    }

    public function delete(int|string $id): bool
    {
        $category = ModuleCategory::find($id);
        if (!$category) {
            return false;
        }
        $category->delete();
        return true;
    }

    public function searchModuleCategory(string $term): Collection
    {
        return ModuleCategory::where('category_name', 'like', '%' . $term . '%')
            ->orderByDesc('ID')
            ->limit(20)
            ->get(['ID', 'category_name']);
    }
}
