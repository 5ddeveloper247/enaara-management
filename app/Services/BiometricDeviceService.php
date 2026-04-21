<?php

namespace App\Services;

use App\Models\BiometricDevice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BiometricDeviceService
{
    public function getList(): Collection
    {
        return BiometricDevice::query()
            ->with(['organization', 'sbu', 'floor', 'creator'])
            ->orderByDesc('id')
            ->get();
    }

    public function getCounts(): array
    {
        $total = BiometricDevice::count();
        $active = BiometricDevice::where('device_status', 'active')->count();
        $inactive = BiometricDevice::where('device_status', 'inactive')->count();
        $faulty = BiometricDevice::where('device_status', 'faulty')->count();

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
        DB::beginTransaction();

        try {
            $row = [
                'organization_id' => (int) $data['organization_id'],
                'sbu_id' => (int) $data['sbu_id'],
                'sbu_floor_id' => (int) $data['sbu_floor_id'],
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
        return BiometricDevice::query()
            ->with(['organization', 'sbu', 'floor', 'creator'])
            ->find($id);
    }

    public function update(int $id, array $data): BiometricDevice
    {
        $device = BiometricDevice::findOrFail($id);

        $device->update([
            'organization_id' => (int) $data['organization_id'],
            'sbu_id' => (int) $data['sbu_id'],
            'sbu_floor_id' => (int) $data['sbu_floor_id'],
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

        return $device->fresh(['organization', 'sbu', 'floor', 'creator']);
    }

    public function destroy(int $id): void
    {
        DB::beginTransaction();

        try {
            $device = BiometricDevice::findOrFail($id);
            $device->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Biometric device delete error: '.$e->getMessage());
            throw $e;
        }
    }
}
