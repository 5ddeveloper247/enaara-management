<?php

namespace App\Services;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrganizationService
{
    public function getOrganizationsList(): Collection
    {
        return Organization::with('parent')->orderByDesc('id')->get();
    }

    public function getOrganizationsCounts(): array
    {
        $total = Organization::count();
        $active = Organization::where('is_active', true)->count();

        return [
            'total' => $total,
            'active' => $active,
            'active_percentage' => $total > 0 ? (int) round(($active / $total) * 100) : 0,
        ];
    }



    public function store(array $data): Organization
    {
        DB::beginTransaction();

        try {
            $organizationData = [
                'parent_id'   => $data['parent_id'] ?? null,
                'name'        => $data['name'],
                'code'        => $data['code'] ?? null,
                'email'       => $data['email'] ?? null,
                'tax_no'      => $data['tax_no'] ?? null,
                'description' => $data['description'] ?? null,
                'address'     => $data['address'] ?? null,
                'is_active'   => $data['is_active'] ?? true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];

            $organization = Organization::create($organizationData);

            DB::commit();

            return $organization;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Organization Store Error: ' . $e->getMessage());

            throw $e;
        }
    }

    public function findById($id): ?Organization
    {
        return Organization::with('parent')->find($id);
    }

    public function update($id, array $data)
    {
        $organization = Organization::findOrFail($id);

        $organization->update([
            'parent_id'   => $data['parent_id'] ?? null,
            'name'        => $data['name'],
            'code'        => $data['code'] ?? null,
            'email'       => $data['email'] ?? null,
            'tax_no'      => $data['tax_no'] ?? null,
            'description' => $data['description'] ?? null,
            'address'     => $data['address'] ?? null,
            'is_active'   => $data['is_active'],
        ]);

        return $organization;
    }



    public function destroy($id): void
    {
        DB::beginTransaction();

        try {
            $organization = Organization::findOrFail($id);

            $organization->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Organization Delete Error: ' . $e->getMessage());

            throw $e;
        }
    }
}
