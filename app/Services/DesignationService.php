<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Organization;
use App\Models\Sbu;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class DesignationService
{
    private function assertSbuBelongsToOrganization(int $sbuId, int $organizationId): void
    {
        $sbu = Sbu::query()->select(['id', 'organization_id'])->find($sbuId);
        if (! $sbu || (int) $sbu->organization_id !== $organizationId) {
            throw ValidationException::withMessages([
                'sbu_id' => ['The selected SBU does not belong to the selected organization.'],
            ]);
        }
    }

    private function assertDepartmentBelongsToSbu(int $departmentId, int $sbuId, int $organizationId): void
    {
        $department = Department::query()->select(['id', 'sbu_id', 'organization_id'])->find($departmentId);
        if (! $department
            || (int) $department->sbu_id !== $sbuId
            || (int) $department->organization_id !== $organizationId) {
            throw ValidationException::withMessages([
                'department_id' => ['The selected department does not belong to the selected SBU and organization.'],
            ]);
        }
    }

    private function normalizeName(string $value): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim($value)));
    }

    private function ensureUniqueName(int $departmentId, string $name, ?int $ignoreId = null): void
    {
        $normalized = $this->normalizeName($name);

        $query = Designation::query()->where('department_id', $departmentId);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $exists = $query->get(['id', 'name'])->contains(function (Designation $item) use ($normalized) {
            return $this->normalizeName((string) $item->name) === $normalized;
        });

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => ['This designation name is already in use for the selected department.'],
            ]);
        }
    }

    public function getList(): Collection
    {
        return Designation::query()
            ->with([
                'organization:id,name',
                'sbu:id,name,organization_id',
                'sbu.organization:id,name',
                'department:id,name,sbu_id',
            ])
            ->where('is_system_generated', false)
            ->orderByDesc('id')
            ->get();
    }

    public function getCounts(): array
    {
        $base    = Designation::where('is_system_generated', false);
        $total   = (clone $base)->count();
        $active  = (clone $base)->where('is_active', true)->count();
        $inactive = (clone $base)->where('is_active', false)->count();

        return [
            'total'            => $total,
            'active'           => $active,
            'inactive'         => $inactive,
            'active_percentage'=> $total > 0 ? (int) round(($active / $total) * 100) : 0,
        ];
    }

    public function findById(int $id): ?Designation
    {
        $designation = Designation::query()
            ->with([
                'organization:id,name',
                'sbu:id,name,organization_id',
                'sbu.organization:id,name',
                'department:id,name,sbu_id',
            ])
            ->find($id);

        // Do not expose system-generated designations through the admin UI
        if ($designation && $designation->is_system_generated) {
            return null;
        }

        return $designation;
    }

    public function create(array $data): Designation
    {
        $payload = Arr::only($data, ['organization_id', 'sbu_id', 'department_id', 'name', 'description', 'is_active']);
        $orgId = (int) $payload['organization_id'];
        $sbuId = (int) $payload['sbu_id'];
        $departmentId = (int) $payload['department_id'];
        $this->assertSbuBelongsToOrganization($sbuId, $orgId);
        $this->assertDepartmentBelongsToSbu($departmentId, $sbuId, $orgId);
        if (! empty($payload['name'])) {
            $this->ensureUniqueName($departmentId, (string) $payload['name']);
        }

        return Designation::create($payload);
    }

    public function update(Designation $designation, array $data): Designation
    {
        if ($designation->is_system_generated) {
            throw ValidationException::withMessages([
                'name' => ['System-generated designations cannot be edited.'],
            ]);
        }

        $payload = Arr::only($data, ['organization_id', 'sbu_id', 'department_id', 'name', 'description', 'is_active']);
        $orgId = (int) ($payload['organization_id'] ?? $designation->organization_id);
        $sbuId = (int) ($payload['sbu_id'] ?? $designation->sbu_id);
        $departmentId = (int) ($payload['department_id'] ?? $designation->department_id);
        $this->assertSbuBelongsToOrganization($sbuId, $orgId);
        $this->assertDepartmentBelongsToSbu($departmentId, $sbuId, $orgId);
        if (! empty($payload['name'])) {
            $this->ensureUniqueName($departmentId, (string) $payload['name'], (int) $designation->id);
        }

        $designation->update($payload);

        return $designation->fresh([
            'organization:id,name',
            'sbu:id,name,organization_id',
            'sbu.organization:id,name',
            'department:id,name,sbu_id',
        ]);
    }

    public function destroy(Designation $designation): bool
    {
        if ($designation->is_system_generated) {
            throw ValidationException::withMessages([
                'name' => ['System-generated designations cannot be deleted.'],
            ]);
        }

        return $designation->delete();
    }

    public function listActiveByOrganizationAndSbu(int $organizationId, int $sbuId, ?int $departmentId = null): array
    {
        $departmentIds = ($departmentId !== null && $departmentId > 0) ? [$departmentId] : [];

        return $this->listActiveByOrganizationSbuAndDepartments($organizationId, $sbuId, $departmentIds);
    }

    public function listActiveByOrganizationSbuAndDepartments(int $organizationId, int $sbuId, array $departmentIds): array
    {
        $this->assertSbuBelongsToOrganization($sbuId, $organizationId);

        $departmentIds = array_values(array_unique(array_filter(array_map('intval', $departmentIds))));
        if ($departmentIds === []) {
            return [];
        }

        foreach ($departmentIds as $departmentId) {
            $this->assertDepartmentBelongsToSbu($departmentId, $sbuId, $organizationId);
        }

        return Designation::query()
            ->select(['id', 'name', 'department_id'])
            ->where('organization_id', $organizationId)
            ->where('sbu_id', $sbuId)
            ->whereIn('department_id', $departmentIds)
            ->where('is_active', true)
            ->where('is_system_generated', false)
            ->orderBy('name')
            ->get()
            ->map(static fn (Designation $d): array => [
                'id' => (int) $d->id,
                'name' => (string) $d->name,
                'department_id' => (int) $d->department_id,
            ])
            ->values()
            ->all();
    }

    public function getOrganizationHierarchy(): Collection
    {
        return Organization::query()
            ->select(['id', 'name'])
            ->where('is_active', true)
            ->with([
                'sbus' => static function ($query): void {
                    $query->select(['id', 'organization_id', 'name'])
                        ->where('is_active', true)
                        ->orderBy('name');
                },
                'sbus.departments' => static function ($query): void {
                    $query->select(['id', 'sbu_id', 'name'])
                        ->where('is_active', true)
                        ->orderBy('name');
                },
            ])
            ->orderBy('name')
            ->get();
    }
}
