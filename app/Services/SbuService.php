<?php

namespace App\Services;

use App\Models\Sbu;
use Illuminate\Database\Eloquent\Collection;

class SbuService
{
    public function getList(): Collection
    {
        return Sbu::with('organization')
            ->orderByDesc('id')
            ->get();
    }

    public function getCounts(): array
    {
        $total = Sbu::count();
        $active = Sbu::where('is_active', true)->count();

        return [
            'total' => $total,
            'active' => $active,
            'active_percentage' => $total > 0 ? (int) round(($active / $total) * 100) : 0,
        ];
    }

    public function findById(int $id): ?Sbu
    {
        return Sbu::with('organization')->find($id);
    }
}
