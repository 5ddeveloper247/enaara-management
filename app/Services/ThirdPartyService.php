<?php

namespace App\Services;

use App\Models\ThirdParty;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ThirdPartyService
{
    public function getList(): Collection
    {
        return ThirdParty::with('organization')
            ->orderByDesc('id')
            ->get();
    }

    public function getCounts(): array
    {
        $total  = ThirdParty::count();
        $active = ThirdParty::where('is_active', true)->count();

        return [
            'total'              => $total,
            'active'             => $active,
            'active_percentage'  => $total > 0 ? (int) round(($active / $total) * 100) : 0,
        ];
    }

    public function store(array $data): ThirdParty
    {
        DB::beginTransaction();

        try {
            $row = [
                'organization_id'  => $data['organization_id'],
                'name'             => $data['name'],
                'third_party_name' => $data['third_party_name'],
                'city'             => $data['city'] ?? null,
                'address'          => $data['address'] ?? null,
                'latitude'         => $data['latitude'] ?? null,
                'longitude'        => $data['longitude'] ?? null,
                'is_active'        => $data['is_active'] ?? true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            $thirdParty = ThirdParty::create($row);

            DB::commit();

            return $thirdParty;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Third Party Store Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function findById($id): ?ThirdParty
    {
        return ThirdParty::with('organization')->find($id);
    }

    public function update($id, array $data): ThirdParty
    {
        $thirdParty = ThirdParty::findOrFail($id);

        $thirdParty->update([
            'organization_id'  => $data['organization_id'],
            'name'             => $data['name'],
            'third_party_name' => $data['third_party_name'],
            'city'             => $data['city'] ?? null,
            'address'          => $data['address'] ?? null,
            'latitude'         => $data['latitude'] ?? null,
            'longitude'        => $data['longitude'] ?? null,
            'is_active'        => $data['is_active'],
        ]);

        return $thirdParty;
    }

    public function destroy($id): void
    {
        DB::beginTransaction();

        try {
            $thirdParty = ThirdParty::findOrFail($id);
            $thirdParty->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Third Party Delete Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
