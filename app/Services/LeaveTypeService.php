<?php

namespace App\Services;

use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeaveTypeService
{
    public function getList(): Collection
    {
        return LeaveType::with(['organization', 'sbu', 'sbus', 'setting'])
            ->orderByDesc('id')
            ->get();
    }

    public function getCounts(): array
    {
        $total = LeaveType::count();
        $active = LeaveType::where('is_active', true)->count();
        $inactive = LeaveType::where('is_active', false)->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
        ];
    }

    public function getEntitlementReference(int $organizationId, array $sbuIds = [], ?int $excludeLeaveTypeId = null): array
    {
        $sbuIds = array_values(array_unique(array_filter(array_map('intval', $sbuIds))));

        $query = LeaveType::query()
            ->select(['id', 'name', 'code', 'annual_quota'])
            ->where('organization_id', $organizationId)
            ->where('is_active', true)
            ->orderBy('name');

        if ($excludeLeaveTypeId) {
            $query->where('id', '!=', $excludeLeaveTypeId);
        }

        if ($sbuIds !== []) {
            $query->where(function ($q) use ($sbuIds) {
                $q->whereIn('sbu_id', $sbuIds)
                    ->orWhereHas('sbus', function ($sq) use ($sbuIds) {
                        $sq->whereIn('sbus.id', $sbuIds);
                    });
            });
        }

        return $query->get()->map(function (LeaveType $leaveType) {
            $quota = (float) $leaveType->annual_quota;
            $days = fmod($quota, 1.0) === 0.0
                ? (string) (int) $quota
                : rtrim(rtrim(number_format($quota, 2, '.', ''), '0'), '.');

            return [
                'id' => $leaveType->id,
                'name' => $leaveType->name,
                'code' => $leaveType->code,
                'days' => $days,
            ];
        })->values()->all();
    }

    public function findById(int $id): ?LeaveType
    {
        return LeaveType::with(['organization', 'sbu', 'sbus', 'setting'])->find($id);
    }

    public function formatForForm(LeaveType $leaveType): array
    {
        $data = [
            'id' => $leaveType->id,
            'organization_id' => $leaveType->organization_id,
            'sbu_id' => $leaveType->sbu_id,
            'sbu_ids' => $leaveType->sbus->pluck('id')->values()->all(),
            'name' => $leaveType->name,
            'code' => $leaveType->code,
            'leave_category' => $leaveType->leave_category,
            'description' => $leaveType->description,
            'annual_quota' => $leaveType->annual_quota,
            'is_active' => $leaveType->is_active,
        ];

        $setting = $leaveType->setting;
        if ($setting) {
            $data = array_merge($data, [
                'employment_type' => $setting->employment_type,
                'gender' => $setting->gender,
                'min_service_months' => $setting->min_service_months,
                'eligible_from' => $setting->eligible_from,
                'probation_eligible' => $setting->probation_eligible,
                'unit_of_leave' => $setting->unit_of_leave,
                'accrual_frequency' => $setting->accrual_frequency,
                'accrual_start_month' => $setting->accrual_start_month,
                'carry_forward' => $setting->carry_forward,
                'max_carry_forward_days' => $setting->max_carry_forward_days,
                'encashment_allowed' => $setting->encashment_allowed,
                'encashment_rule' => $setting->encashment_rule,
                'max_consecutive_days' => $setting->max_consecutive_days,
                'advance_notice_days' => $setting->advance_notice_days,
                'short_leave_applicable' => $setting->short_leave_applicable,
                'short_leave_max_hours' => $setting->short_leave_max_hours,
            ]);
        }

        return $data;
    }

    private function normalizeSbuIds(array $data): array
    {
        $ids = $data['sbu_ids'] ?? [];
        if (! is_array($ids)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('intval', $ids))));
    }

    private function extractMainAttributes(array $data): array
    {
        return Arr::only($data, [
            'organization_id',
            'name',
            'code',
            'leave_category',
            'description',
            'annual_quota',
            'is_active',
        ]);
    }

    private function extractSettingAttributes(array $data): array
    {
        return [
            'employment_type' => $data['employment_type'] ?? 'all',
            'gender' => $data['gender'] ?? 'all',
            'min_service_months' => (int) ($data['min_service_months'] ?? 0),
            'eligible_from' => $data['eligible_from'] ?? 'doj',
            'probation_eligible' => (bool) ($data['probation_eligible'] ?? false),
            'unit_of_leave' => $data['unit_of_leave'] ?? 'days',
            'accrual_frequency' => $data['accrual_frequency'] ?? null,
            'accrual_start_month' => filled($data['accrual_start_month'] ?? null)
                ? (int) $data['accrual_start_month']
                : null,
            'carry_forward' => $data['carry_forward'] ?? 'no',
            'max_carry_forward_days' => filled($data['max_carry_forward_days'] ?? null)
                ? $data['max_carry_forward_days']
                : null,
            'encashment_allowed' => $data['encashment_allowed'] ?? 'no',
            'encashment_rule' => $data['encashment_rule'] ?? null,
            'max_consecutive_days' => filled($data['max_consecutive_days'] ?? null)
                ? (int) $data['max_consecutive_days']
                : null,
            'advance_notice_days' => (int) ($data['advance_notice_days'] ?? 0),
            'short_leave_applicable' => (bool) ($data['short_leave_applicable'] ?? false),
            'short_leave_max_hours' => filled($data['short_leave_max_hours'] ?? null)
                ? (int) $data['short_leave_max_hours']
                : null,
        ];
    }

    public function store(array $data): LeaveType
    {
        DB::beginTransaction();

        try {
            $sbuIds = $this->normalizeSbuIds($data);
            $attributes = $this->extractMainAttributes($data);
            $attributes['sbu_id'] = $sbuIds[0] ?? null;

            $leaveType = LeaveType::create($attributes);
            $leaveType->setting()->create($this->extractSettingAttributes($data));
            $leaveType->sbus()->sync($sbuIds);
            $leaveType->departments()->detach();

            DB::commit();

            return $leaveType->fresh(['organization', 'sbu', 'sbus', 'setting']);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Leave type store failed: ' . $e->getMessage());

            throw $e;
        }
    }

    public function update(int $id, array $data): LeaveType
    {
        DB::beginTransaction();

        try {
            $leaveType = LeaveType::findOrFail($id);
            $sbuIds = $this->normalizeSbuIds($data);
            $attributes = $this->extractMainAttributes($data);
            $attributes['sbu_id'] = $sbuIds[0] ?? null;

            $leaveType->update($attributes);

            $settingPayload = $this->extractSettingAttributes($data);
            if ($leaveType->setting) {
                $leaveType->setting()->update($settingPayload);
            } else {
                $leaveType->setting()->create($settingPayload);
            }

            $leaveType->sbus()->sync($sbuIds);
            $leaveType->departments()->detach();

            DB::commit();

            return $leaveType->fresh(['organization', 'sbu', 'sbus', 'setting']);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Leave type update failed: ' . $e->getMessage());

            throw $e;
        }
    }

    public function destroy(int $id): void
    {
        DB::beginTransaction();

        try {
            $leaveType = LeaveType::findOrFail($id);

            $leaveType->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Leave type delete failed: ' . $e->getMessage());

            throw $e;
        }
    }
}
