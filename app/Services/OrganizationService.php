<?php

namespace App\Services;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Collection;

class OrganizationService
{
    public function getOrganizationsList(): Collection
    {
        return Organization::with('parent')->orderByDesc('id')->get();
    }

    public function getOrganizationsCounts(): array
    {
        $total = Organization::count();
        $active = Organization::where('is_active', true)->count();

        return [
            'total' => $total,
            'active' => $active,
            'active_percentage' => $total > 0 ? (int) round(($active / $total) * 100) : 0,
        ];
    }
}
