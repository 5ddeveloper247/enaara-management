<?php

namespace App\Services;

use App\Models\BiometricDevice;
use App\Models\SbuFloor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SbuFloorService
{
    public function getList(): Collection
    {
        return SbuFloor::query()
            ->with([
                'sbu.organization',
                'biometricDevices' => function ($query) {
                    $query->orderByDesc('id');
                },
            ])
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
            $floorPayload = Arr::only($data, [
                'sbu_id',
                'name',
                'floor_number',
                'floor_type',
                'is_restricted',
                'is_active',
            ]);

            $floorPayload['floor_number'] = $floorPayload['floor_number'] ?? null;
            $floorPayload['is_restricted'] = $floorPayload['is_restricted'] ?? false;
            $floorPayload['is_active'] = $floorPayload['is_active'] ?? true;
            $floorPayload['created_at'] = now();
            $floorPayload['updated_at'] = now();

            $sbuFloor = SbuFloor::create($floorPayload);

            $this->syncBiometricDevicesForFloor(
                (int) $sbuFloor->id,
                (int) $sbuFloor->sbu_id,
                $data['biometric_device_ids'] ?? []
            );

            DB::commit();

            return $sbuFloor;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('SBU Floor Store Error: '.$e->getMessage());

            throw $e;
        }
    }

    public function findById($id): ?SbuFloor
    {
        return SbuFloor::query()
            ->with([
                'sbu.organization',
                'biometricDevices' => function ($query) {
                    $query->orderByDesc('id');
                },
            ])
            ->find($id);
    }

    public function update($id, array $data): SbuFloor
    {
        DB::beginTransaction();

        try {
            $sbuFloor = SbuFloor::findOrFail($id);
            $oldSbuId = (int) $sbuFloor->sbu_id;
            $newSbuId = (int) $data['sbu_id'];

            if ($oldSbuId !== $newSbuId) {
                BiometricDevice::query()
                    ->where('sbu_floor_id', $sbuFloor->id)
                    ->update(['sbu_floor_id' => null]);
            }

            $sbuFloor->update([
                'sbu_id' => $newSbuId,
                'name' => $data['name'],
                'floor_number' => $data['floor_number'] ?? null,
                'floor_type' => $data['floor_type'],
                'is_restricted' => $data['is_restricted'],
                'is_active' => $data['is_active'],
            ]);

            $this->syncBiometricDevicesForFloor(
                (int) $sbuFloor->id,
                $newSbuId,
                $data['biometric_device_ids'] ?? []
            );

            DB::commit();

            return $sbuFloor->fresh();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('SBU Floor Update Error: '.$e->getMessage());

            throw $e;
        }
    }

    public function destroy($id): void
    {
        DB::beginTransaction();

        try {
            $sbuFloor = SbuFloor::findOrFail($id);

            BiometricDevice::query()
                ->where('sbu_floor_id', $sbuFloor->id)
                ->update(['sbu_floor_id' => null]);

            $sbuFloor->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('SBU Floor Delete Error: '.$e->getMessage());

            throw $e;
        }
    }

    protected function syncBiometricDevicesForFloor(int $floorId, int $sbuId, array $selectedDeviceIds): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $selectedDeviceIds))));

        BiometricDevice::query()
            ->where('sbu_id', $sbuId)
            ->whereIn('id', $ids)
            ->update(['sbu_floor_id' => $floorId]);

        BiometricDevice::query()
            ->where('sbu_id', $sbuId)
            ->where('sbu_floor_id', $floorId)
            ->whereNotIn('id', $ids)
            ->update(['sbu_floor_id' => null]);
    }
}
