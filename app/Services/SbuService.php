<?php

namespace App\Services;

use App\Models\Sbu;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    public function store(array $data): Sbu
    {
        DB::beginTransaction();

        try {
            $sbuData = [
                'organization_id' => $data['organization_id'],
                'name'            => $data['name'],
                'city'            => $data['city'] ?? null,
                'address'         => $data['address'] ?? null,
                'latitude'        => $data['latitude'] ?? null,
                'longitude'       => $data['longitude'] ?? null,
                'working_days'    => $data['working_days'] ?? null,
                'working_start_time' => $data['working_start_time'] ?? null,
                'working_end_time' => $data['working_end_time'] ?? null,
                'opening_grace_period' => $data['opening_grace_period'] ?? null,
                'closing_grace_period' => $data['closing_grace_period'] ?? null,
                'is_active'       => $data['is_active'] ?? true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ];

            $sbu = Sbu::create($sbuData);

            DB::commit();

            return $sbu;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('SBU Store Error: ' . $e->getMessage());

            throw $e;
        }
    }

    public function findById($id): ?Sbu
    {
        return Sbu::with('organization')->find($id);
    }

    public function update($id, array $data): Sbu
    {
        $sbu = Sbu::findOrFail($id);

        $sbu->update([
            'organization_id' => $data['organization_id'],
            'name'            => $data['name'],
            'city'            => $data['city'] ?? null,
            'address'         => $data['address'] ?? null,
            'latitude'        => $data['latitude'] ?? null,
            'longitude'       => $data['longitude'] ?? null,
            'working_days'    => $data['working_days'] ?? null,
            'working_start_time' => $data['working_start_time'] ?? null,
            'working_end_time' => $data['working_end_time'] ?? null,
            'opening_grace_period' => $data['opening_grace_period'] ?? null,
            'closing_grace_period' => $data['closing_grace_period'] ?? null,
            'is_active'       => $data['is_active'],
        ]);

        return $sbu;
    }

    public function destroy($id): void
    {
        DB::beginTransaction();

        try {
            $sbu = Sbu::findOrFail($id);

            $sbu->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('SBU Delete Error: ' . $e->getMessage());

            throw $e;
        }
    }
}