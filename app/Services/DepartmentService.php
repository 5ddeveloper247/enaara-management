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
        return Sbu::orderBy('name')->get([
            'id',
            'name',
            'organization_id',
            'working_days',
            'working_start_time',
            'working_end_time',
            'opening_grace_period',
            'closing_grace_period',
        ]);
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
        $data = $this->withSyncedGracePeriod($data);

        return Department::create($data);
    }

    public function update(Department $department, array $data): Department
    {
        $data = $this->withSyncedGracePeriod($data);
        $department->update($data);

        return $department->fresh(['organization', 'sbu', 'parent']);
    }

    private function withSyncedGracePeriod(array $data): array
    {
        $gracePeriod = $data['opening_grace_period'] ?? null;
        if ($gracePeriod === null && array_key_exists('closing_grace_period', $data)) {
            $gracePeriod = $data['closing_grace_period'];
        }
        $data['opening_grace_period'] = $gracePeriod;
        $data['closing_grace_period'] = $gracePeriod;

        return $data;
    }

    public function destroy(Department $department): bool
    {
        return $department->delete();
    }
}
