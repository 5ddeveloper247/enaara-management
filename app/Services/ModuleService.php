<?php

namespace App\Services;

use App\Models\Module;
use App\Models\ModuleCategory;
use Illuminate\Database\Eloquent\Collection;

class ModuleService
{
    public function getList(): Collection
    {
        return Module::with('moduleCategory')
            ->orderByDesc('id')
            ->get();
    }

    public function getModuleCategoriesForSelect(): Collection
    {
        return ModuleCategory::orderBy('display_order')
            ->orderBy('category_name')
            ->get(['ID', 'category_name']);
    }

    public function getCounts(): array
    {
        $total = Module::count();
        $active = Module::where('show_in_menu', 1)->count();
        $inactive = Module::where('show_in_menu', 0)->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
        ];
    }

    public function findById(int $id): ?Module
    {
        return Module::with('moduleCategory')->find($id);
    }

    public function create(array $data): Module
    {
        return Module::create($data);
    }

    public function update(Module $module, array $data): Module
    {
        $module->update($data);
        return $module->fresh();
    }

    public function updateStatus(int $id, bool $showInMenu): ?Module
    {
        $module = Module::find($id);
        if (!$module) {
            return null;
        }
        $module->show_in_menu = $showInMenu ? 1 : 0;
        $module->save();
        return $module;
    }

    public function delete(int $id): bool
    {
        $module = Module::find($id);
        if (!$module) {
            return false;
        }
        $module->delete();
        return true;
    }

    public function searchModule(string $term): Collection
    {
        return Module::where('module_name', 'like', '%' . $term . '%')
            ->orderByDesc('id')
            ->limit(20)
            ->get(['id', 'module_name']);
    }
}
