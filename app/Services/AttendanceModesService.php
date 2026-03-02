<?php

namespace App\Services;

use App\Models\AttendanceModel;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Collection;

class AttendanceModesService
{
    public function getList(): Collection
    {
        return AttendanceModel::with('organization')
            ->orderByDesc('id')
            ->get();
    }

    public function getOrganizationsForFilter(): Collection
    {
        return Organization::orderBy('name')->get(['id', 'name']);
    }

    public function getCounts(): array
    {
        $total = AttendanceModel::count();
        $active = AttendanceModel::where('is_active', true)->count();
        $inactive = AttendanceModel::where('is_active', false)->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
        ];
    }

    public function updateStatus(int $id, bool $isActive): ?AttendanceModel
    {
        $model = AttendanceModel::find($id);
        if (!$model) {
            return null;
        }
        $model->is_active = $isActive;
        $model->save();
        return $model;
    }

    public function delete(int $id): bool
    {
        $model = AttendanceModel::find($id);
        if (!$model) {
            return false;
        }
        $model->delete();
        return true;
    }
}
