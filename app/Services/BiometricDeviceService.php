<?php

namespace App\Services;

use App\Models\BiometricDevice;
use App\Models\Organization;
use App\Models\Sbu;
use App\Services\ViewerScope\BiometricDeviceViewerScopeService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BiometricDeviceService
{
    public function __construct(
        private readonly BiometricDeviceViewerScopeService $biometricDeviceScope,
        private readonly EmployeeViewerScopeService $viewerScope,
    ) {}

    public function getList(): Collection
    {
        $query = BiometricDevice::query()
            ->with(['organization', 'sbu', 'creator'])
            ->orderByDesc('id');

        $this->biometricDeviceScope->applyQueryScope($query);

        return $query->get();
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

    public function getCounts(): array
    {
        $base = BiometricDevice::query();
        $this->biometricDeviceScope->applyQueryScope($base);

        $total = (clone $base)->count();
        $active = (clone $base)->where('device_status', 'active')->count();
        $inactive = (clone $base)->where('device_status', 'inactive')->count();
        $faulty = (clone $base)->where('device_status', 'faulty')->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'faulty' => $faulty,
            'active_percentage' => $total > 0 ? (int) round(($active / $total) * 100) : 0,
        ];
    }

    public function store(array $data): BiometricDevice
    {
        $this->assertWriteDataAllowed($data);

        DB::beginTransaction();

        try {
            $row = [
                'organization_id' => (int) $data['organization_id'],
                'sbu_id' => (int) $data['sbu_id'],
                'device_name' => $data['device_name'],
                'serial_number' => $data['serial_number'],
                'device_type' => $data['device_type'],
                'brand_model' => $data['brand_model'],
                'ip_address' => $data['ip_address'],
                'port' => (int) $data['port'],
                'connection_type' => $data['connection_type'],
                'device_status' => $data['device_status'],
                'online_status' => 'unknown',
                'last_sync_time' => null,
                'installation_date' => $data['installation_date'],
                'created_by' => auth()->id(),
            ];

            $device = BiometricDevice::create($row);
            DB::commit();

            return $device;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Biometric device store error: '.$e->getMessage());
            throw $e;
        }
    }

    public function findById(int $id): ?BiometricDevice
    {
        $query = BiometricDevice::query()
            ->with(['organization', 'sbu', 'creator']);

        $this->biometricDeviceScope->applyQueryScope($query);

        return $query->find($id);
    }

    public function update(int $id, array $data): BiometricDevice
    {
        $device = $this->findById($id);
        if ($device === null) {
            throw ValidationException::withMessages([
                'biometric_device' => ['Biometric device not found or outside your SBU scope.'],
            ]);
        }

        $this->biometricDeviceScope->assertIdAccessible((int) $device->id);
        $this->assertWriteDataAllowed($data);

        $device->update([
            'organization_id' => (int) $data['organization_id'],
            'sbu_id' => (int) $data['sbu_id'],
            'device_name' => $data['device_name'],
            'serial_number' => $data['serial_number'],
            'device_type' => $data['device_type'],
            'brand_model' => $data['brand_model'],
            'ip_address' => $data['ip_address'],
            'port' => (int) $data['port'],
            'connection_type' => $data['connection_type'],
            'device_status' => $data['device_status'],
            'installation_date' => $data['installation_date'],
        ]);

        return $device->fresh(['organization', 'sbu', 'creator']);
    }

    public function destroy(int $id): void
    {
        DB::beginTransaction();

        try {
            $device = $this->findById($id);
            if ($device === null) {
                throw ValidationException::withMessages([
                    'biometric_device' => ['Biometric device not found or outside your SBU scope.'],
                ]);
            }

            $this->biometricDeviceScope->assertIdAccessible((int) $device->id);
            $device->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Biometric device delete error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertWriteDataAllowed(array $data): void
    {
        $this->viewerScope->assertSbuIdAllowed((int) ($data['sbu_id'] ?? 0));
    }
}
