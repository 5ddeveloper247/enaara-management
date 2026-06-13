<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\ShiftPlanner;
use App\Services\ViewerScope\ShiftViewerScopeService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ShiftPlannerService
{
    public function __construct(
        private readonly ShiftViewerScopeService $shiftScope,
        private readonly EmployeeViewerScopeService $viewerScope,
    ) {}

    public function getOrganizationHierarchy(): Collection
    {
        $organizations = Organization::query()
            ->select(['id', 'name'])
            ->where('is_active', true)
            ->with([
                'sbus' => static function ($query): void {
                    $query->select(['id', 'organization_id', 'name'])
                        ->where('is_active', true)
                        ->orderBy('name');
                },
            ])
            ->orderBy('name')
            ->get();

        return $this->viewerScope->filterOrganizations($organizations);
    }

    public function getActiveList(): Collection
    {
        $query = ShiftPlanner::query()
            ->with(['organization:id,name', 'sbu:id,name'])
            ->where('is_active', true)
            ->orderBy('name');

        $this->shiftScope->applyPlannerQueryScope($query);

        return $query->get();
    }

    public function getAllForManagement(): Collection
    {
        $query = ShiftPlanner::query()
            ->with(['organization:id,name', 'sbu:id,name'])
            ->orderBy('name');

        $this->shiftScope->applyPlannerQueryScope($query);

        return $query->get();
    }

    public function findAccessible(int $id): ?ShiftPlanner
    {
        $query = ShiftPlanner::query()->with(['organization:id,name', 'sbu:id,name']);
        $this->shiftScope->applyPlannerQueryScope($query);

        return $query->find($id);
    }

    /**
     * Store a newly created shift planner.
     */
    public function store(array $data)
    {
        DB::beginTransaction();

        try {
            $ownership = $this->shiftScope->resolvePlannerOwnership($data);
            $this->ensureUniqueShiftName($data, null, $ownership['sbu_id']);
            $this->ensureUniqueShiftTime($data, null, $ownership['sbu_id']);

            $shiftPlannerData = [
                'organization_id' => $ownership['organization_id'],
                'sbu_id' => $ownership['sbu_id'],
                'name' => $data['name'],
                'code' => $data['code'] ?? null,
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'clock_in_window_minutes' => $data['clock_in_window_minutes'],
                'clock_out_window_minutes' => $data['clock_out_window_minutes'],
                'shift_duration_minutes' => $this->calculateShiftDuration($data['start_time'], $data['end_time']),
                'grace_period_minutes' => $data['grace_period_minutes'],
                'break_time_minutes' => $data['break_time_minutes'],
                'overtime_allowed' => $data['overtime_allowed'],
                'overtime_trigger_hours' => ! empty($data['overtime_allowed']) ? ($data['overtime_trigger_hours'] ?? null) : null,
                'is_active' => $data['is_active'] ?? true,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ];

            $shiftPlanner = ShiftPlanner::create($shiftPlannerData);

            DB::commit();

            return $shiftPlanner;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Shift Planner Store Error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing shift planner.
     */
    public function update(array $data, $id)
    {
        DB::beginTransaction();

        try {
            $shiftPlanner = $this->findAccessible((int) $id);
            if ($shiftPlanner === null) {
                throw ValidationException::withMessages([
                    'shift' => ['Shift not found or outside your SBU scope.'],
                ]);
            }

            $this->shiftScope->assertPlannerIdAccessible((int) $shiftPlanner->id);
            $ownership = $this->shiftScope->resolvePlannerOwnership($data);
            $this->ensureUniqueShiftName($data, (int) $id, $ownership['sbu_id']);
            $this->ensureUniqueShiftTime($data, (int) $id, $ownership['sbu_id']);

            $shiftPlannerData = [
                'organization_id' => $ownership['organization_id'],
                'sbu_id' => $ownership['sbu_id'],
                'name' => $data['name'],
                'code' => $data['code'] ?? null,
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'clock_in_window_minutes' => $data['clock_in_window_minutes'],
                'clock_out_window_minutes' => $data['clock_out_window_minutes'],
                'shift_duration_minutes' => $this->calculateShiftDuration($data['start_time'], $data['end_time']),
                'grace_period_minutes' => $data['grace_period_minutes'],
                'break_time_minutes' => $data['break_time_minutes'],
                'overtime_allowed' => $data['overtime_allowed'],
                'overtime_trigger_hours' => ! empty($data['overtime_allowed']) ? ($data['overtime_trigger_hours'] ?? null) : null,
                'is_active' => $data['is_active'] ?? $shiftPlanner->is_active,
                'updated_by' => auth()->id(),
            ];

            $shiftPlanner->update($shiftPlannerData);

            DB::commit();

            return $shiftPlanner;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Shift Planner Update Error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a shift planner.
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $shiftPlanner = $this->findAccessible((int) $id);
            if ($shiftPlanner === null) {
                throw ValidationException::withMessages([
                    'shift' => ['Shift not found or outside your SBU scope.'],
                ]);
            }

            $this->shiftScope->assertPlannerIdAccessible((int) $shiftPlanner->id);
            $shiftPlanner->delete();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Shift Planner Deletion Error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Calculate shift duration in minutes.
     */
    private function calculateShiftDuration(string $startTime, string $endTime): int
    {
        $start = Carbon::createFromFormat('H:i', $startTime);
        $end = Carbon::createFromFormat('H:i', $endTime);

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return $start->diffInMinutes($end);
    }

    private function ensureUniqueShiftName(array $data, ?int $ignoreId = null, ?int $sbuId = null): void
    {
        $existsQuery = ShiftPlanner::query()->where('name', $data['name']);

        if ($ignoreId) {
            $existsQuery->where('id', '!=', $ignoreId);
        }

        if ($sbuId !== null) {
            $existsQuery->where('sbu_id', $sbuId);
        }

        if ($existsQuery->exists()) {
            throw ValidationException::withMessages([
                'name' => ['Shift name already exists.'],
            ]);
        }
    }

    private function ensureUniqueShiftTime(array $data, ?int $ignoreId = null, ?int $sbuId = null): void
    {
        $existsQuery = ShiftPlanner::query()
            ->where('start_time', $data['start_time'])
            ->where('end_time', $data['end_time']);

        if ($ignoreId) {
            $existsQuery->where('id', '!=', $ignoreId);
        }

        if ($sbuId !== null) {
            $existsQuery->where('sbu_id', $sbuId);
        }

        if ($existsQuery->exists()) {
            throw ValidationException::withMessages([
                'start_time' => ['Shift already registered on this time.'],
            ]);
        }
    }
}
