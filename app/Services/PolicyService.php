<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\Policy;
use App\Models\Sbu;
use App\Models\SbuFloor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PolicyService
{
    /**
     * Store a newly created policy.
     */
    public function store(array $data, $file = null)
    {
        DB::beginTransaction();
        try {
            $scope = $this->resolveScopeFields($data);

            $policyData = [
                'title' => $data['title'],
                'category' => $data['category'],
                'status' => $data['status'],
                'effective_date' => $data['effective_date'],
                'applicable_to' => $data['applicable_to'],
                'applicable_details' => $scope['applicable_details'],
                'organization_id' => $scope['organization_id'],
                'sbu_id' => $scope['sbu_id'],
                'sbu_floor_id' => $scope['sbu_floor_id'],
                'description' => $data['description'] ?? null,
            ];

            if ($file) {
                $path = $file->store('policies', 'public');
                $policyData['document_path'] = $path;
                $policyData['document_name'] = $file->getClientOriginalName();
            }

            $policy = Policy::create($policyData);

            DB::commit();

            return $policy;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Policy Store Error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing policy.
     */
    public function update(array $data, $id, $file = null)
    {
        DB::beginTransaction();
        try {
            $policy = Policy::findOrFail($id);

            $scope = $this->resolveScopeFields($data);

            $policyData = [
                'title' => $data['title'],
                'category' => $data['category'],
                'status' => $data['status'],
                'effective_date' => $data['effective_date'],
                'applicable_to' => $data['applicable_to'],
                'applicable_details' => $scope['applicable_details'],
                'organization_id' => $scope['organization_id'],
                'sbu_id' => $scope['sbu_id'],
                'sbu_floor_id' => $scope['sbu_floor_id'],
                'description' => $data['description'] ?? null,
            ];

            if ($file) {
                // Delete old file if exists
                if ($policy->document_path && Storage::disk('public')->exists($policy->document_path)) {
                    Storage::disk('public')->delete($policy->document_path);
                }
                $path = $file->store('policies', 'public');
                $policyData['document_path'] = $path;
                $policyData['document_name'] = $file->getClientOriginalName();
            }

            // If user explicitly removed the document
            if (! empty($data['remove_document']) && ! $file) {
                if ($policy->document_path && Storage::disk('public')->exists($policy->document_path)) {
                    Storage::disk('public')->delete($policy->document_path);
                }
                $policyData['document_path'] = null;
                $policyData['document_name'] = null;
            }

            $policy->update($policyData);

            DB::commit();

            return $policy;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Policy Update Error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a policy.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $policy = Policy::findOrFail($id);

            // Delete associated document
            if ($policy->document_path && Storage::disk('public')->exists($policy->document_path)) {
                Storage::disk('public')->delete($policy->document_path);
            }

            $policy->delete();
            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Policy Deletion Error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * @return array{organization_id: ?int, sbu_id: ?int, sbu_floor_id: ?int, applicable_details: ?string}
     */
    protected function resolveScopeFields(array $data): array
    {
        $applicableTo = $data['applicable_to'] ?? 'global';

        if ($applicableTo === 'global') {
            return [
                'organization_id' => null,
                'sbu_id' => null,
                'sbu_floor_id' => null,
                'applicable_details' => 'Global (All Organizations)',
            ];
        }

        if ($applicableTo === 'organization') {
            $organizationId = isset($data['organization_id']) ? (int) $data['organization_id'] : null;
            $name = $organizationId ? Organization::query()->whereKey($organizationId)->value('name') : null;

            return [
                'organization_id' => $organizationId ?: null,
                'sbu_id' => null,
                'sbu_floor_id' => null,
                'applicable_details' => $name,
            ];
        }

        if ($applicableTo === 'sbu') {
            $sbuId = isset($data['sbu_id']) ? (int) $data['sbu_id'] : null;
            $sbu = $sbuId ? Sbu::query()->find($sbuId) : null;

            return [
                'organization_id' => $sbu?->organization_id,
                'sbu_id' => $sbu?->id,
                'sbu_floor_id' => null,
                'applicable_details' => $sbu ? ($sbu->name.' (SBU)') : null,
            ];
        }

        if ($applicableTo === 'floor') {
            $floorId = isset($data['sbu_floor_id']) ? (int) $data['sbu_floor_id'] : null;
            $floor = $floorId ? SbuFloor::query()->with('sbu')->find($floorId) : null;
            $sbu = $floor?->sbu;

            $details = null;
            if ($floor && $sbu) {
                $details = trim($floor->name.' — '.$sbu->name);
            }

            return [
                'organization_id' => $sbu?->organization_id,
                'sbu_id' => $sbu?->id,
                'sbu_floor_id' => $floor?->id,
                'applicable_details' => $details,
            ];
        }

        return [
            'organization_id' => null,
            'sbu_id' => null,
            'sbu_floor_id' => null,
            'applicable_details' => $data['applicable_details'] ?? null,
        ];
    }
}
