<?php

namespace App\Services;

use App\Models\OutsourcedEmployee;
use App\Models\SbuFloor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OutsourcedEmployeeService
{
    public function getTableData(array $filters = []): array
    {
        $query = OutsourcedEmployee::query()
            ->with(['organization:id,name', 'sbu:id,name', 'department:id,name', 'contractorCompany:id,third_party_name', 'assignedFloors:id,name']);

        $organization = trim((string) ($filters['filter_organization'] ?? ''));
        $sbu = trim((string) ($filters['filter_sbu'] ?? ''));
        $department = trim((string) ($filters['filter_department'] ?? ''));
        $name = trim((string) ($filters['filter_name'] ?? ''));
        $cnic = preg_replace('/\D/', '', (string) ($filters['filter_cnic'] ?? ''));

        if ($organization !== '') {
            $query->whereHas('organization', function ($q) use ($organization) {
                $q->where('name', 'like', '%' . $organization . '%');
            });
        }

        if ($sbu !== '') {
            $query->whereHas('sbu', function ($q) use ($sbu) {
                $q->where('name', 'like', '%' . $sbu . '%');
            });
        }

        if ($department !== '') {
            $query->whereHas('department', function ($q) use ($department) {
                $q->where('name', 'like', '%' . $department . '%');
            });
        }

        if ($name !== '') {
            $query->where('full_name', 'like', '%' . $name . '%');
        }

        if ($cnic !== '') {
            $query->where('cnic_number', 'like', '%' . $cnic . '%');
        }

        return $query
            ->orderByDesc('id')
            ->get()
            ->map(function (OutsourcedEmployee $row): array {
                return [
                    'id' => $row->id,
                    'full_name' => $row->full_name,
                    'cnic_number' => $row->cnic_number,
                    'mobile_number' => $row->mobile_number,
                    'photo_url' => $row->photo_path ? asset('storage/' . $row->photo_path) : null,
                    'contractor_company_id' => $row->contractor_company_id,
                    'contractor_company_name' => $row->contractorCompany?->third_party_name ?? '-',
                    'supervisor_name' => $row->supervisor_name,
                    'supervisor_contact_number' => $row->supervisor_contact_number,
                    'organization_id' => $row->organization_id,
                    'organization' => $row->organization?->name ?? '-',
                    'sbu_id' => $row->sbu_id,
                    'sbu' => $row->sbu?->name ?? '-',
                    'department_id' => $row->department_id,
                    'department' => $row->department?->name ?? '-',
                    'job_role_trade' => $row->job_role_trade,
                    'placement_floor' => $row->assignedFloors->pluck('name')->implode(', '),
                    'date_of_deployment' => optional($row->date_of_deployment)->format('Y-m-d'),
                    'biometric_id' => $row->biometric_id,
                    'attendance_access' => (bool) $row->attendance_access,
                ];
            })
            ->values()
            ->all();
    }

    public function store(array $data, ?UploadedFile $photo = null): OutsourcedEmployee
    {
        return DB::transaction(function () use ($data, $photo) {
            $floorIds = $data['assigned_floor_ids'] ?? [];
            unset($data['assigned_floor_ids']);
            $data['placement_floor'] = $this->resolvePlacementFloor((array) $floorIds);
            
            $row = OutsourcedEmployee::create($data);
            if (!empty($floorIds)) {
                $row->assignedFloors()->sync($floorIds);
            }
            if ($photo) {
                $path = $photo->store("employees/outsourced/{$row->id}/profile", 'public');
                $row->update(['photo_path' => $path]);
            }
            return $row->fresh(['organization:id,name', 'sbu:id,name', 'department:id,name', 'contractorCompany:id,third_party_name', 'assignedFloors']);
        });
    }

    public function update(int $id, array $data, ?UploadedFile $photo = null): OutsourcedEmployee
    {
        return DB::transaction(function () use ($id, $data, $photo) {
            $row = OutsourcedEmployee::query()->findOrFail($id);
            $floorIds = $data['assigned_floor_ids'] ?? [];
            unset($data['assigned_floor_ids']);
            $data['placement_floor'] = $this->resolvePlacementFloor((array) $floorIds);
            
            $row->assignedFloors()->sync($floorIds);
            
            if ($photo) {
                if (! empty($row->photo_path) && Storage::disk('public')->exists($row->photo_path)) {
                    Storage::disk('public')->delete($row->photo_path);
                }
                $data['photo_path'] = $photo->store("employees/outsourced/{$row->id}/profile", 'public');
            }
            $row->update($data);
            return $row->fresh(['organization:id,name', 'sbu:id,name', 'department:id,name', 'contractorCompany:id,third_party_name', 'assignedFloors']);
        });
    }

    private function resolvePlacementFloor(array $floorIds): string
    {
        $floorIds = collect($floorIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($floorIds === []) {
            return '';
        }

        return SbuFloor::query()
            ->whereIn('id', $floorIds)
            ->orderBy('name')
            ->pluck('name')
            ->filter()
            ->implode(', ');
    }

    public function findForEdit(int $id): OutsourcedEmployee
    {
        $employee = OutsourcedEmployee::query()->with(['organization:id,name', 'sbu:id,name', 'department:id,name', 'contractorCompany:id,third_party_name', 'assignedFloors:id,name'])->findOrFail($id);
        $employee->assigned_floor_ids = $employee->assignedFloors->pluck('id')->toArray();
        return $employee;
    }
}

