<?php

namespace App\Services;

use App\Models\SbuFloor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    public function store(array $data): SbuFloor
    {
        DB::beginTransaction();

        try {
            $sbuFloorData = [
                'sbu_id'         => $data['sbu_id'],
                'name'           => $data['name'],
                'floor_number'   => $data['floor_number'] ?? null,
                'floor_type'     => $data['floor_type'],
                'is_restricted'  => $data['is_restricted'] ?? false,
                'is_active'      => $data['is_active'] ?? true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];

            $sbuFloor = SbuFloor::create($sbuFloorData);

            DB::commit();

            return $sbuFloor;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('SBU Floor Store Error: ' . $e->getMessage());

            throw $e;
        }
    }

    public function findById($id): ?SbuFloor
    {
        return SbuFloor::with('sbu')->find($id);
    }

    public function update($id, array $data): SbuFloor
    {
        $sbuFloor = SbuFloor::findOrFail($id);

        $sbuFloor->update([
            'sbu_id'         => $data['sbu_id'],
            'name'           => $data['name'],
            'floor_number'   => $data['floor_number'] ?? null,
            'floor_type'     => $data['floor_type'],
            'is_restricted'  => $data['is_restricted'],
            'is_active'      => $data['is_active'],
        ]);

        return $sbuFloor;
    }

    public function destroy($id): void
    {
        DB::beginTransaction();

        try {
            $sbuFloor = SbuFloor::findOrFail($id);

            $sbuFloor->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('SBU Floor Delete Error: ' . $e->getMessage());

            throw $e;
        }
    }
}
