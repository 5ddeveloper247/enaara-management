<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\ShiftType;
use App\Services\ViewerScope\ShiftViewerScopeService;
use Illuminate\Database\Eloquent\Collection;

class ShiftTypeService
{
    public function __construct(
        private readonly ShiftViewerScopeService $shiftScope,
        private readonly EmployeeViewerScopeService $viewerScope,
    ) {}

    public function getList(): Collection
    {
        $query = ShiftType::query()
            ->with(['organization', 'department'])
            ->orderByDesc('id');

        $this->shiftScope->applyShiftTypeQueryScope($query);

        return $query->get();
    }

    public function getOrganizationsForFilter(): Collection
    {
        $organizations = Organization::orderBy('name')->get(['id', 'name']);

        return $this->viewerScope->filterOrganizations($organizations);
    }

    public function getCounts(): array
    {
        $base = ShiftType::query();
        $this->shiftScope->applyShiftTypeQueryScope($base);

        $total = (clone $base)->count();
        $active = (clone $base)->where('is_active', true)->count();
        $inactive = (clone $base)->where('is_active', false)->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
        ];
    }

    public function updateStatus(int $id, bool $isActive): ?ShiftType
    {
        $query = ShiftType::query();
        $this->shiftScope->applyShiftTypeQueryScope($query);

        $shiftType = $query->find($id);
        if (! $shiftType) {
            return null;
        }

        $this->shiftScope->assertShiftTypeIdAccessible((int) $shiftType->id);
        $shiftType->is_active = $isActive;
        $shiftType->save();

        return $shiftType;
    }

    public function delete(int $id): bool
    {
        $query = ShiftType::query();
        $this->shiftScope->applyShiftTypeQueryScope($query);

        $shiftType = $query->find($id);
        if (! $shiftType) {
            return false;
        }

        $this->shiftScope->assertShiftTypeIdAccessible((int) $shiftType->id);
        $shiftType->delete();

        return true;
    }
}
