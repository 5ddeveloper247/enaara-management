<?php

namespace App\Services;

use App\Models\BiometricDevice;
use App\Models\Organization;
use App\Models\Sbu;
use App\Models\SbuFloor;
use App\Services\ViewerScope\BiometricDeviceViewerScopeService;
use App\Services\ViewerScope\SbuFloorViewerScopeService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SbuFloorService
{
    public function __construct(
        private readonly SbuFloorViewerScopeService $sbuFloorScope,
        private readonly BiometricDeviceViewerScopeService $biometricDeviceScope,
        private readonly EmployeeViewerScopeService $viewerScope,
    ) {}

    public function getList(): Collection
    {
        $query = SbuFloor::query()
            ->with([
                'sbu.organization',
                'biometricDevices' => function ($query) {
                    $query->orderByDesc('id');
                },
            ])
            ->orderByDesc('id');

        $this->sbuFloorScope->applyQueryScope($query);

        return $query->get();
    }

    public function getCounts(): array
    {
        $base = SbuFloor::query();
        $this->sbuFloorScope->applyQueryScope($base);

        $total = (clone $base)->count();
        $active = (clone $base)->where('is_active', true)->count();

        return [
            'total' => $total,
            'active' => $active,
            'active_percentage' => $total > 0 ? (int) round(($active / $total) * 100) : 0,
        ];
    }

    public function getOrganizationsForFilter(): Collection
    {
        $organizations = Organization::query()
            ->select(['id', 'name'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return $this->viewerScope->filterOrganizations($organizations);
    }

    public function getSbusForFilter(): Collection
    {
        $sbus = Sbu::query()
            ->select(['id', 'name', 'organization_id'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return $this->viewerScope->filterSbus($sbus);
    }

    public function getBiometricDevicesForForms(): Collection
    {
        $query = BiometricDevice::query()
            ->orderByDesc('id');

        $this->biometricDeviceScope->applyQueryScope($query);

        return $query->get(['id', 'sbu_id', 'device_name', 'serial_number', 'sbu_floor_id']);
    }

    public function store(array $data): SbuFloor
    {
        $this->viewerScope->assertSbuIdAllowed((int) ($data['sbu_id'] ?? 0));

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
        $query = SbuFloor::query()
            ->with([
                'sbu.organization',
                'biometricDevices' => function ($query) {
                    $query->orderByDesc('id');
                },
            ]);

        $this->sbuFloorScope->applyQueryScope($query);

        return $query->find($id);
    }

    public function update($id, array $data): SbuFloor
    {
        DB::beginTransaction();

        try {
            $sbuFloor = $this->findById((int) $id);
            if ($sbuFloor === null) {
                throw ValidationException::withMessages([
                    'sbu_floor' => ['SBU floor not found or outside your SBU scope.'],
                ]);
            }

            $this->sbuFloorScope->assertIdAccessible((int) $sbuFloor->id);
            $this->viewerScope->assertSbuIdAllowed((int) ($data['sbu_id'] ?? 0));

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
            $sbuFloor = $this->findById((int) $id);
            if ($sbuFloor === null) {
                throw ValidationException::withMessages([
                    'sbu_floor' => ['SBU floor not found or outside your SBU scope.'],
                ]);
            }

            $this->sbuFloorScope->assertIdAccessible((int) $sbuFloor->id);

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
