<?php

namespace App\Services;

use App\Models\LeaveType;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Collection;

class LeaveTypeService
{
    public function getList(): Collection
    {
        return LeaveType::with('organization')
            ->orderByDesc('id')
            ->get();
    }

    public function getOrganizationsForFilter(): Collection
    {
        return Organization::orderBy('name')->get(['id', 'name']);
    }

    public function getCounts(): array
    {
        $total = LeaveType::count();
        $active = LeaveType::where('is_active', true)->count();
        $inactive = LeaveType::where('is_active', false)->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
        ];
    }

    public function findById(int $id): ?LeaveType
    {
        return LeaveType::with('organization')->find($id);
    }

    public function create(array $data): LeaveType
    {
        return LeaveType::create($data);
    }

    public function update(LeaveType $leaveType, array $data): LeaveType
    {
        $leaveType->update($data);

        return $leaveType->fresh('organization');
    }
}

