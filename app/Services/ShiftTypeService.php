<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\ShiftType;
use Illuminate\Database\Eloquent\Collection;

class ShiftTypeService
{
    public function getList(): Collection
    {
        return ShiftType::with('organization')
            ->orderByDesc('id')
            ->get();
    }

    public function getOrganizationsForFilter(): Collection
    {
        return Organization::orderBy('name')->get(['id', 'name']);
    }

    public function getCounts(): array
    {
        $total = ShiftType::count();
        $active = ShiftType::where('is_active', true)->count();
        $inactive = ShiftType::where('is_active', false)->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
        ];
    }

    public function updateStatus(int $id, bool $isActive): ?ShiftType
    {
        $shiftType = ShiftType::find($id);
        if (!$shiftType) {
            return null;
        }
        $shiftType->is_active = $isActive;
        $shiftType->save();
        return $shiftType;
    }

    public function delete(int $id): bool
    {
        $shiftType = ShiftType::find($id);
        if (!$shiftType) {
            return false;
        }
        $shiftType->delete();
        return true;
    }
}
