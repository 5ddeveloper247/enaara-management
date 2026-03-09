<?php

namespace App\Services;

use App\Models\EmployeeType;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Collection;

class EmployeeTypeService
{
    public function getList(): Collection
    {
        return EmployeeType::with(['organization', 'department'])
            ->orderByDesc('id')
            ->get();
    }

    public function getOrganizationsForFilter(): Collection
    {
        return Organization::orderBy('name')->get(['id', 'name']);
    }

    public function getCounts(): array
    {
        $total = EmployeeType::count();
        $active = EmployeeType::where('is_active', true)->count();
        $inactive = EmployeeType::where('is_active', false)->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
        ];
    }

    public function updateStatus(int $id, bool $isActive): ?EmployeeType
    {
        $employeeType = EmployeeType::find($id);
        if (!$employeeType) {
            return null;
        }
        $employeeType->is_active = $isActive;
        $employeeType->save();
        return $employeeType;
    }

    public function delete(int $id): bool
    {
        $employeeType = EmployeeType::find($id);
        if (!$employeeType) {
            return false;
        }
        $employeeType->delete();
        return true;
    }
}
