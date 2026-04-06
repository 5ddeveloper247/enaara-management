<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Organization;
use App\Models\Sbu;
use Illuminate\Database\Eloquent\Collection;

class DepartmentService
{
    public function getList(): Collection
    {
        return Department::with(['organization', 'sbu', 'parent'])
            ->orderByDesc('id')
            ->get();
    }

    public function getOrganizationsForFilter(): Collection
    {
        return Organization::orderBy('name')->get(['id', 'name']);
    }

    public function getSbusForFilter(): Collection
    {
        return Sbu::orderBy('name')->get(['id', 'name']);
    }

    public function getCounts(): array
    {
        $total = Department::count();
        $active = Department::where('is_active', true)->count();
        $inactive = Department::where('is_active', false)->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'active_percentage' => $total > 0 ? (int) round(($active / $total) * 100) : 0,
        ];
    }

    public function findById(int $id): ?Department
    {
        return Department::with(['organization', 'sbu', 'parent'])->find($id);
    }

    public function create(array $data): Department
    {
        return Department::create($data);
    }

    public function update(Department $department, array $data): Department
    {
        $department->update($data);
        return $department->fresh(['organization', 'sbu', 'parent']);
    }

    public function destroy(Department $department): bool
    {
        return $department->delete();
    }
}
