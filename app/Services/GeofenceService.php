<?php

namespace App\Services;

use App\Models\Geofence;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class GeofenceService
{
    /**
     * Store a newly created geofence in storage with DB transaction.
     * 
     * @param array $data Validated data from request
     * @return Geofence
     * @throws \Exception
     */
    public function store(array $data)
    {
        try {
            DB::beginTransaction();
            $this->ensureUniqueSiteName($data);

            $geofence = Geofence::create([
                'name' => $data['siteName'],
                'address' => $data['address'],
                'latitude' => $data['lat'],
                'longitude' => $data['lng'],
                'radius' => $data['radius'],
                'radius_unit' => $data['radiusUnit'],
                'type' => $data['type'],
                'organization_id' => $data['organization_id'],
                'sbu_id' => $data['sbu_id'],
                'anti_spoofing' => $data['antiSpoofing'] ?? false,
                'offline_sync' => $data['offlineSync'] ?? true,
                'auto_check_in' => $data['autoCheckIn'] ?? false,
                'status' => 'active',
            ]);

            DB::commit();

            return $geofence;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating Geofence: ' . $e->getMessage());
            throw $e;
        }
    }
    /**
     * Update an existing geofence.
     * 
     * @param Geofence $geofence
     * @param array $data
     * @return Geofence
     * @throws \Exception
     */
    public function update(Geofence $geofence, array $data)
    {
        try {
            DB::beginTransaction();
            $this->ensureUniqueSiteName($data, (int) $geofence->id);

            $geofence->update([
                'name' => $data['siteName'],
                'address' => $data['address'],
                'latitude' => $data['lat'],
                'longitude' => $data['lng'],
                'radius' => $data['radius'],
                'radius_unit' => $data['radiusUnit'],
                'type' => $data['type'],
                'organization_id' => $data['organization_id'],
                'sbu_id' => $data['sbu_id'],
                'anti_spoofing' => $data['antiSpoofing'] ?? $geofence->anti_spoofing,
                'offline_sync' => $data['offlineSync'] ?? $geofence->offline_sync,
                'auto_check_in' => $data['autoCheckIn'] ?? $geofence->auto_check_in,
                'status' => $data['status'] ?? $geofence->status,
            ]);

            DB::commit();

            return $geofence;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating Geofence: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a geofence.
     * 
     * @param Geofence $geofence
     * @return bool
     * @throws \Exception
     */
    public function destroy(Geofence $geofence)
    {
        try {
            DB::beginTransaction();
            $geofence->delete();
            DB::commit();
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting Geofence: ' . $e->getMessage());
            throw $e;
        }
    }

    private function ensureUniqueSiteName(array $data, ?int $ignoreId = null): void
    {
        $existsQuery = Geofence::query()
            ->where('name', $data['siteName'])
            ->where('organization_id', $data['organization_id'])
            ->where('sbu_id', $data['sbu_id']);

        if ($ignoreId) {
            $existsQuery->where('id', '!=', $ignoreId);
        }

        if ($existsQuery->exists()) {
            throw ValidationException::withMessages([
                'siteName' => ['Site name already exists for the selected organization and SBU.'],
            ]);
        }
    }
}
