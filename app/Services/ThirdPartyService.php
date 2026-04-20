<?php

namespace App\Services;

use App\Models\Sbu;
use App\Models\ThirdParty;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ThirdPartyService
{
    protected function sanitizeIds(array $ids): array
    {
        $clean = array_values(array_unique(array_map('intval', $ids)));
        sort($clean);
        return $clean;
    }

    protected function resolvePrimaryOrganizationId(array $organizationIds, array $sbuIds, ?int $fallback = null): ?int
    {
        $organizationIds = $this->sanitizeIds($organizationIds);
        $sbuIds = $this->sanitizeIds($sbuIds);

        if ($sbuIds !== []) {
            $derivedOrganizationIds = Sbu::query()
                ->whereIn('id', $sbuIds)
                ->pluck('organization_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            $derivedOrganizationIds = $this->sanitizeIds($derivedOrganizationIds);

            if ($derivedOrganizationIds !== []) {
                return $derivedOrganizationIds[0];
            }
        }

        if ($organizationIds !== []) {
            return $organizationIds[0];
        }

        return $fallback;
    }

    public function getList(): Collection
    {
        return ThirdParty::with(['organization', 'organizations', 'sbus'])
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
            $organizationIds = $this->sanitizeIds($data['organization_ids'] ?? []);
            $sbuIds = $this->sanitizeIds($data['sbu_ids'] ?? []);
            $primaryOrganizationId = $this->resolvePrimaryOrganizationId($organizationIds, $sbuIds);

            $row = [
                'organization_id'  => $primaryOrganizationId,
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
            $thirdParty->organizations()->sync($organizationIds);
            $thirdParty->sbus()->sync($sbuIds);

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
        return ThirdParty::with(['organization', 'organizations', 'sbus'])->find($id);
    }

    public function update($id, array $data): ThirdParty
    {
        $thirdParty = ThirdParty::findOrFail($id);
        $organizationIds = $this->sanitizeIds($data['organization_ids'] ?? []);
        $sbuIds = $this->sanitizeIds($data['sbu_ids'] ?? []);
        $primaryOrganizationId = $this->resolvePrimaryOrganizationId(
            $organizationIds,
            $sbuIds,
            $thirdParty->organization_id ? (int) $thirdParty->organization_id : null
        );

        $thirdParty->update([
            'organization_id'  => $primaryOrganizationId,
            'third_party_name' => $data['third_party_name'],
            'city'             => $data['city'] ?? null,
            'address'          => $data['address'] ?? null,
            'latitude'         => $data['latitude'] ?? null,
            'longitude'        => $data['longitude'] ?? null,
            'is_active'        => $data['is_active'],
        ]);
        $thirdParty->organizations()->sync($organizationIds);
        $thirdParty->sbus()->sync($sbuIds);

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
