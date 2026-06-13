<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Organization;
use App\Models\Sbu;
use App\Services\ViewerScope\DepartmentViewerScopeService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class DepartmentService
{
    public function __construct(
        private readonly DepartmentViewerScopeService $departmentScope,
        private readonly EmployeeViewerScopeService $viewerScope,
    ) {}

    public function getList(): Collection
    {
        $query = Department::query()
            ->with(['organization', 'sbu', 'parent'])
            ->orderByDesc('id');

        $this->departmentScope->applyQueryScope($query);

        return $query->get();
    }

    public function getOrganizationsForFilter(): Collection
    {
        $organizations = Organization::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        return $this->viewerScope->filterOrganizations($organizations);
    }

    public function getSbusForFilter(): Collection
    {
        $sbus = Sbu::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'organization_id',
                'working_days',
                'working_start_time',
                'working_end_time',
                'opening_grace_period',
                'closing_grace_period',
            ]);

        return $this->viewerScope->filterSbus($sbus);
    }

    public function getParentDepartmentsForForm(?int $excludeId = null): Collection
    {
        $query = Department::query()
            ->with('organization')
            ->orderBy('name');

        $this->departmentScope->applyQueryScope($query);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->get(['id', 'name', 'organization_id', 'sbu_id']);
    }

    public function getCounts(): array
    {
        $base = Department::query();
        $this->departmentScope->applyQueryScope($base);

        $total = (clone $base)->count();
        $active = (clone $base)->where('is_active', true)->count();
        $inactive = (clone $base)->where('is_active', false)->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'active_percentage' => $total > 0 ? (int) round(($active / $total) * 100) : 0,
        ];
    }

    public function findById(int $id): ?Department
    {
        $query = Department::query()
            ->with(['organization', 'sbu', 'parent']);

        $this->departmentScope->applyQueryScope($query);

        return $query->find($id);
    }

    public function create(array $data): Department
    {
        $this->assertDepartmentWriteDataAllowed($data);

        $data = $this->withSyncedGracePeriod($data);

        return Department::create($data);
    }

    public function update(Department $department, array $data): Department
    {
        if (! $this->departmentScope->belongsToViewerScope($department)) {
            throw ValidationException::withMessages([
                'department' => 'This department is outside your SBU scope.',
            ]);
        }

        $this->assertDepartmentWriteDataAllowed($data);

        $data = $this->withSyncedGracePeriod($data);
        $department->update($data);

        return $department->fresh(['organization', 'sbu', 'parent']);
    }

    private function assertDepartmentWriteDataAllowed(array $data): void
    {
        $this->viewerScope->assertSbuIdAllowed((int) ($data['sbu_id'] ?? 0));

        if (! empty($data['parent_department_id'])) {
            $this->viewerScope->assertDepartmentIdAllowed((int) $data['parent_department_id']);
        }
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
        if (! $this->departmentScope->belongsToViewerScope($department)) {
            throw ValidationException::withMessages([
                'department' => 'This department is outside your SBU scope.',
            ]);
        }

        return $department->delete();
    }
}
