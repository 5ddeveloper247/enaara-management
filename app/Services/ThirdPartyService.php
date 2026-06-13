<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\Sbu;
use App\Models\ThirdParty;
use App\Services\ViewerScope\ThirdPartyViewerScopeService;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ThirdPartyService
{
    public function __construct(
        private readonly ThirdPartyViewerScopeService $thirdPartyScope,
        private readonly EmployeeViewerScopeService $viewerScope,
    ) {}

    protected function buildVendorId(int $id): string
    {
        return 'VND-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

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
        $query = ThirdParty::query()
            ->with(['organization', 'organizations', 'sbus'])
            ->orderByDesc('id');

        $this->thirdPartyScope->applyQueryScope($query);

        return $query->get();
    }

    public function getOrganizationsForFilter(): Collection
    {
        $organizations = Organization::query()
            ->select(['id', 'name'])
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
        $base = ThirdParty::query();
        $this->thirdPartyScope->applyQueryScope($base);

        $total = (clone $base)->count();
        $active = (clone $base)->where('is_active', true)->count();

        return [
            'total'              => $total,
            'active'             => $active,
            'active_percentage'  => $total > 0 ? (int) round(($active / $total) * 100) : 0,
        ];
    }

    public function store(array $data, ?UploadedFile $companyRegistrationDocument = null, ?UploadedFile $contractCopy = null): ThirdParty
    {
        $this->thirdPartyScope->assertWriteDataAllowed($data);

        DB::beginTransaction();

        try {
            $organizationIds = $this->sanitizeIds($data['organization_ids'] ?? []);
            $sbuIds = $this->sanitizeIds($data['sbu_ids'] ?? []);
            $primaryOrganizationId = $this->resolvePrimaryOrganizationId($organizationIds, $sbuIds);

            $row = ThirdParty::create([
                'organization_id'  => $primaryOrganizationId,
                'vendor_id'        => null,
                'third_party_name' => $data['third_party_name'],
                'service_type'     => $data['service_type'],
                'specify_service_type' => ($data['service_type'] ?? '') === 'Other' ? ($data['specify_service_type'] ?? null) : null,
                'is_individual_contractor' => (bool) ($data['is_individual_contractor'] ?? false),
                'ntn'              => $data['ntn'] ?? null,
                'contractor_cnic'  => $data['contractor_cnic'] ?? null,
                'contact_person_name' => $data['contact_person_name'],
                'mobile_number'    => $data['mobile_number'],
                'email'            => $data['email'],
                'supervisor_name'  => $data['supervisor_name'],
                'supervisor_cnic'  => $data['supervisor_cnic'],
                'supervisor_mobile_number' => $data['supervisor_mobile_number'],
                'contract_start_date' => $data['contract_start_date'],
                'contract_end_date' => $data['contract_end_date'],
                'scope_of_work'    => $data['scope_of_work'],
                'estimated_staff_count' => $data['estimated_staff_count'],
                'remarks'          => $data['remarks'] ?? null,
                'city'             => $data['city'] ?? null,
                'address'          => $data['address'] ?? null,
                'latitude'         => $data['latitude'] ?? null,
                'longitude'        => $data['longitude'] ?? null,
                'is_active'        => $data['is_active'] ?? true,
            ]);

            $updatePayload = [
                'vendor_id' => $this->buildVendorId((int) $row->id),
            ];
            if ($companyRegistrationDocument) {
                $updatePayload['company_registration_document_path'] = $companyRegistrationDocument->store("third-parties/{$row->id}/documents", 'public');
            }
            if ($contractCopy) {
                $updatePayload['contract_copy_path'] = $contractCopy->store("third-parties/{$row->id}/documents", 'public');
            }
            $row->update($updatePayload);

            $thirdParty = $row->fresh();
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
        $query = ThirdParty::query()
            ->with(['organization', 'organizations', 'sbus']);

        $this->thirdPartyScope->applyQueryScope($query);

        return $query->find($id);
    }

    public function update($id, array $data, ?UploadedFile $companyRegistrationDocument = null, ?UploadedFile $contractCopy = null): ThirdParty
    {
        return DB::transaction(function () use ($id, $data, $companyRegistrationDocument, $contractCopy) {
            $thirdParty = $this->findById((int) $id);
            if ($thirdParty === null) {
                throw ValidationException::withMessages([
                    'third_party' => ['Third party not found or outside your SBU scope.'],
                ]);
            }

            $this->thirdPartyScope->assertIdAccessible((int) $thirdParty->id);
            $this->thirdPartyScope->assertWriteDataAllowed($data);
            $organizationIds = $this->sanitizeIds($data['organization_ids'] ?? []);
            $sbuIds = $this->sanitizeIds($data['sbu_ids'] ?? []);
            $primaryOrganizationId = $this->resolvePrimaryOrganizationId(
                $organizationIds,
                $sbuIds,
                $thirdParty->organization_id ? (int) $thirdParty->organization_id : null
            );

            $updatePayload = [
                'organization_id'  => $primaryOrganizationId,
                'vendor_id'        => $thirdParty->vendor_id ?: $this->buildVendorId((int) $thirdParty->id),
                'third_party_name' => $data['third_party_name'],
                'service_type'     => $data['service_type'],
                'specify_service_type' => ($data['service_type'] ?? '') === 'Other' ? ($data['specify_service_type'] ?? null) : null,
                'is_individual_contractor' => (bool) ($data['is_individual_contractor'] ?? false),
                'ntn'              => ! empty($data['is_individual_contractor']) ? null : ($data['ntn'] ?? null),
                'contractor_cnic'  => ! empty($data['is_individual_contractor']) ? ($data['contractor_cnic'] ?? null) : null,
                'contact_person_name' => $data['contact_person_name'],
                'mobile_number'    => $data['mobile_number'],
                'email'            => $data['email'],
                'supervisor_name'  => $data['supervisor_name'],
                'supervisor_cnic'  => $data['supervisor_cnic'],
                'supervisor_mobile_number' => $data['supervisor_mobile_number'],
                'contract_start_date' => $data['contract_start_date'],
                'contract_end_date' => $data['contract_end_date'],
                'scope_of_work'    => $data['scope_of_work'],
                'estimated_staff_count' => $data['estimated_staff_count'],
                'remarks'          => $data['remarks'] ?? null,
                'city'             => $data['city'] ?? null,
                'address'          => $data['address'] ?? null,
                'latitude'         => $data['latitude'] ?? null,
                'longitude'        => $data['longitude'] ?? null,
                'is_active'        => $data['is_active'],
            ];

            if ($companyRegistrationDocument) {
                if (! empty($thirdParty->company_registration_document_path) && Storage::disk('public')->exists($thirdParty->company_registration_document_path)) {
                    Storage::disk('public')->delete($thirdParty->company_registration_document_path);
                }
                $updatePayload['company_registration_document_path'] = $companyRegistrationDocument->store("third-parties/{$thirdParty->id}/documents", 'public');
            }

            if ($contractCopy) {
                if (! empty($thirdParty->contract_copy_path) && Storage::disk('public')->exists($thirdParty->contract_copy_path)) {
                    Storage::disk('public')->delete($thirdParty->contract_copy_path);
                }
                $updatePayload['contract_copy_path'] = $contractCopy->store("third-parties/{$thirdParty->id}/documents", 'public');
            }

            $thirdParty->update($updatePayload);
            $thirdParty->organizations()->sync($organizationIds);
            $thirdParty->sbus()->sync($sbuIds);

            return $thirdParty->fresh();
        });
    }

    public function destroy($id): void
    {
        DB::beginTransaction();

        try {
            $thirdParty = $this->findById((int) $id);
            if ($thirdParty === null) {
                throw ValidationException::withMessages([
                    'third_party' => ['Third party not found or outside your SBU scope.'],
                ]);
            }

            $this->thirdPartyScope->assertIdAccessible((int) $thirdParty->id);
            $thirdParty->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Third Party Delete Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
