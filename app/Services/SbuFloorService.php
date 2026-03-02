<?php

namespace App\Services;

use App\Models\SbuFloor;
use Illuminate\Database\Eloquent\Collection;

class SbuFloorService
{
    public function getList(): Collection
    {
        return SbuFloor::with('sbu')
            ->orderByDesc('id')
            ->get();
    }

    public function getCounts(): array
    {
        $total = SbuFloor::count();
        $active = SbuFloor::where('is_active', true)->count();

        return [
            'total' => $total,
            'active' => $active,
            'active_percentage' => $total > 0 ? (int) round(($active / $total) * 100) : 0,
        ];
    }

    public function findById(int $id): ?SbuFloor
    {
        return SbuFloor::with('sbu')->find($id);
    }
}
