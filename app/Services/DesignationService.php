<?php

namespace App\Services;

use App\Models\Designation;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class DesignationService
{
    private function normalizeName(string $value): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim($value)));
    }

    private function ensureUniqueName(int $sbuId, string $name, ?int $ignoreId = null): void
    {
        $normalized = $this->normalizeName($name);

        $query = Designation::query()->where('sbu_id', $sbuId);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $exists = $query->get(['id', 'name'])->contains(function (Designation $item) use ($normalized) {
            return $this->normalizeName((string) $item->name) === $normalized;
        });

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => ['This designation name is already in use for the selected SBU.'],
            ]);
        }
    }

    public function getList(): Collection
    {
        return Designation::query()
            ->with([
                'sbu:id,name,organization_id',
                'sbu.organization:id,name',
            ])
            ->orderByDesc('id')
            ->get();
    }

    public function getCounts(): array
    {
        $total = Designation::count();
        $active = Designation::where('is_active', true)->count();
        $inactive = Designation::where('is_active', false)->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'active_percentage' => $total > 0 ? (int) round(($active / $total) * 100) : 0,
        ];
    }

    public function findById(int $id): ?Designation
    {
        return Designation::query()
            ->with([
                'sbu:id,name,organization_id',
                'sbu.organization:id,name',
            ])
            ->find($id);
    }

    public function create(array $data): Designation
    {
        $payload = Arr::only($data, ['sbu_id', 'name', 'description', 'is_active']);
        $sbuId = (int) $payload['sbu_id'];
        if (! empty($payload['name'])) {
            $this->ensureUniqueName($sbuId, (string) $payload['name']);
        }

        return Designation::create($payload);
    }

    public function update(Designation $designation, array $data): Designation
    {
        $payload = Arr::only($data, ['sbu_id', 'name', 'description', 'is_active']);
        $sbuId = (int) ($payload['sbu_id'] ?? $designation->sbu_id);
        if (! empty($payload['name'])) {
            $this->ensureUniqueName($sbuId, (string) $payload['name'], (int) $designation->id);
        }

        $designation->update($payload);

        return $designation->fresh([
            'sbu:id,name,organization_id',
            'sbu.organization:id,name',
        ]);
    }

    public function destroy(Designation $designation): bool
    {
        return $designation->delete();
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
            ])
            ->orderBy('name')
            ->get();
    }
}
