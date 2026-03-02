<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\WorkModel;
use Illuminate\Database\Eloquent\Collection;

class WorkTypeService
{
    public function getList(): Collection
    {
        return WorkModel::with('organization')
            ->orderByDesc('id')
            ->get();
    }

    public function getOrganizationsForFilter(): Collection
    {
        return Organization::orderBy('name')->get(['id', 'name']);
    }

    public function getCounts(): array
    {
        $total = WorkModel::count();
        $active = WorkModel::where('is_active', true)->count();
        $inactive = WorkModel::where('is_active', false)->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
        ];
    }

    public function updateStatus(int $id, bool $isActive): ?WorkModel
    {
        $workModel = WorkModel::find($id);
        if (!$workModel) {
            return null;
        }
        $workModel->is_active = $isActive;
        $workModel->save();
        return $workModel;
    }

    public function delete(int $id): bool
    {
        $workModel = WorkModel::find($id);
        if (!$workModel) {
            return false;
        }
        $workModel->delete();
        return true;
    }
}
