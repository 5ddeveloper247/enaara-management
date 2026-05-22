<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\OutsourcedEmployee;
use App\Models\SbuFloor;
use App\Models\ShiftPlanner;
use App\Models\ShiftRosterEntry;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShiftRosterService
{
    public function __construct(
        private readonly ShiftRosterHistoryService $historyService,
        private readonly PublicHolidayResolver $publicHolidayResolver,
        private readonly CompensatoryLeaveAwardService $compensatoryLeaveAwardService,
    ) {
    }

    private const WEEKDAYS = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];

    public function floorOptionsForAssignee(string $employeeType, int $employeeId): array
    {
        return $this->mapFloorOptions($this->resolveAssigneeFloors($employeeType, $employeeId));
    }

    public function floorOptionsForBulkAssignees(array $employeeRefs): array
    {
        $refs = $this->parseAssigneeRefs($employeeRefs);
        if ($refs === []) {
            return [];
        }

        $sbuIds = collect($refs)
            ->map(fn (array $ref) => $this->resolveAssigneeSbuId($ref['type'], $ref['id']))
            ->filter()
            ->unique()
            ->values();

        if ($sbuIds->isEmpty()) {
            return [];
        }

        $floors = SbuFloor::query()
            ->whereIn('sbu_id', $sbuIds)
            ->where('is_active', true)
            ->orderBy('floor_number')
            ->orderBy('name')
            ->get()
            ->unique('id');

        return $this->mapFloorOptions($floors);
    }

    public function assigneeSupportsFloor(string $employeeType, int $employeeId, int $floorId): bool
    {
        $sbuId = $this->resolveAssigneeSbuId($employeeType, $employeeId);
        if (! $sbuId) {
            return false;
        }

        return SbuFloor::query()
            ->whereKey($floorId)
            ->where('sbu_id', $sbuId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Store a newly created shift roster (Single Day).
     */
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $userId = Auth::id();
            $assignment = $this->buildEntryAssignment($data);
            $payload = [
                'shift_planner_id' => $assignment['shift_planner_id'],
                'is_custom_time' => $assignment['is_custom_time'],
                'roster_date' => $data['roster_date'],
                'start_time' => $assignment['start_time'],
                'end_time' => $assignment['end_time'],
                'check_in' => $data['check_in'] ?? null,
                'check_out' => $data['check_out'] ?? null,
                'late_check_in' => (bool) ($data['late_check_in'] ?? false),
                'status' => (($data['status'] ?? 1) == 1) ? 'pending' : 'cancelled',
                'assignment_id' => null,
            ];
            $this->applyFloorToPayload($data, $payload);
            $this->applyLocationToPayload($data, $payload);
            $this->applyNotesToPayload($data, $payload);

            if (($data['employee_type'] ?? 'employee') === 'outsourced') {
                $payload['employee_id'] = null;
                $payload['outsourced_employee_id'] = (int) $data['employee_id'];
                $lookup = [
                    'outsourced_employee_id' => (int) $data['employee_id'],
                    'roster_date' => $data['roster_date'],
                ];
            } else {
                $payload['employee_id'] = (int) $data['employee_id'];
                $payload['outsourced_employee_id'] = null;
                $lookup = [
                    'employee_id' => (int) $data['employee_id'],
                    'roster_date' => $data['roster_date'],
                ];
            }

            return $this->saveDailyRosterEntry($lookup, $payload, $userId);
        });
    }

    /**
     * Update an existing shift roster entry.
     */
    public function update(array $data, $id)
    {
        $entry = ShiftRosterEntry::findOrFail($id);
        $userId = Auth::id();
        $assignment = $this->buildEntryAssignment($data, $entry);
        $payload = [
            'shift_planner_id' => $assignment['shift_planner_id'],
            'is_custom_time' => $assignment['is_custom_time'],
            'roster_date' => $data['roster_date'],
            'start_time' => $assignment['start_time'],
            'end_time' => $assignment['end_time'],
            'check_in' => $data['check_in'] ?? $entry->check_in,
            'check_out' => $data['check_out'] ?? $entry->check_out,
            'late_check_in' => (bool) ($data['late_check_in'] ?? $entry->late_check_in),
            'status' => isset($data['status']) ? ((int) $data['status'] === 1 ? 'pending' : 'cancelled') : $entry->status,
        ];
        $this->applyFloorToPayload($data, $payload, $entry->floor);
        $this->applyLocationToPayload($data, $payload, $entry->location_text);
        $this->applyNotesToPayload($data, $payload);

        if (($data['employee_type'] ?? 'employee') === 'outsourced') {
            $payload['outsourced_employee_id'] = (int) $data['employee_id'];
            $payload['employee_id'] = null;
            $payload['compensatory_reason'] = null;
        } else {
            $payload['employee_id'] = (int) $data['employee_id'];
            $payload['outsourced_employee_id'] = null;
            $this->compensatoryLeaveAwardService->applyCompensatoryReasonToPayload(
                $payload,
                $entry,
                $entry->employee,
                (string) $data['roster_date']
            );
        }

        $before = $this->historyService->snapshot($entry);
        $entry->update($payload);
        if ($userId) {
            $entry->updated_by = $userId;
            $entry->save();
        }
        $entry->refresh();
        $this->historyService->recordUpdated($entry, $before, $userId);

        return $entry;
    }

    /**
     * Delete a shift roster entry.
     */
    public function destroy($id)
    {
        $entry = ShiftRosterEntry::findOrFail($id);
        $userId = Auth::id();
        if ($userId) {
            $entry->deleted_by = $userId;
            $entry->save();
        }
        $this->historyService->recordDeleted($entry, $userId);

        return $entry->delete();
    }

    /**
     * Bulk assign shifts to multiple employees across a date range.
     */
    public function bulkAssign(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $isCustomTime = $this->isCustomTimeRequest($data);
            $shiftPlannerId = null;
            $entryStartTime = null;
            $entryEndTime = null;

            if ($isCustomTime) {
                $entryStartTime = $this->formatShiftTime($data['start_time']);
                $entryEndTime = $this->formatShiftTime($data['end_time']);
            } elseif (! empty($data['shift_planner_id'])) {
                $shiftPlannerId = (int) $data['shift_planner_id'];
                if (! empty($data['start_time']) && ! empty($data['end_time'])) {
                    $entryStartTime = $this->formatShiftTime($data['start_time']);
                    $entryEndTime = $this->formatShiftTime($data['end_time']);
                }
            } else {
                throw new \InvalidArgumentException('Select a shift or enable custom start and end time.');
            }
            $refs = $this->parseAssigneeRefs($data['employee_ids'] ?? []);
            $days = array_map('strtolower', $data['days'] ?? []);
            $offDays = array_map('strtolower', $data['off_days'] ?? []);
            if ($offDays === []) {
                $offDays = array_values(array_diff(self::WEEKDAYS, $days));
            }
            $excludeWeekends = (bool) ($data['exclude_weekends'] ?? false);
            $checkConflicts = (bool) ($data['check_conflicts'] ?? true);
            $overrideExisting = (bool) ($data['override_existing'] ?? false);
            $workingConflicts = [];

            if ($checkConflicts && ! $overrideExisting && $days !== []) {
                $workingConflicts = $this->collectBulkConflicts(
                    $refs,
                    $data['start_date'],
                    $data['end_date'],
                    $days
                );
            }

            $skipWorkingDays = $workingConflicts !== [];

            $placementData = [
                'sbu_floor_id' => $data['sbu_floor_id'] ?? null,
                'location_text' => $data['location_text'] ?? null,
                'notes' => $data['notes'] ?? null,
            ];

            $period = CarbonPeriod::create($data['start_date'], $data['end_date']);
            $totalAssigned = 0;
            $totalOffDays = 0;

            foreach ($period as $date) {
                $dayName = strtolower($date->format('l'));
                if ($excludeWeekends && in_array($dayName, ['saturday', 'sunday'], true)) {
                    continue;
                }

                $isWorkingDay = in_array($dayName, $days, true);
                $isOffDay = in_array($dayName, $offDays, true);
                if (! $isWorkingDay && ! $isOffDay) {
                    continue;
                }

                foreach ($refs as $ref) {
                    if ($isWorkingDay) {
                        if ($skipWorkingDays) {
                            continue;
                        }

                        $this->upsertAssigneeShiftEntry(
                            $ref['type'],
                            $ref['id'],
                            $date->toDateString(),
                            $overrideExisting,
                            $shiftPlannerId,
                            $isCustomTime,
                            $entryStartTime,
                            $entryEndTime,
                            $placementData
                        );
                        $totalAssigned++;
                        continue;
                    }

                    $this->upsertAssigneeOffEntry(
                        $ref['type'],
                        $ref['id'],
                        $date->toDateString(),
                        $shiftPlannerId,
                        $overrideExisting
                    );
                    $totalOffDays++;
                }
            }

            if ($skipWorkingDays && $totalAssigned === 0 && $totalOffDays === 0) {
                return [
                    'success' => false,
                    'message' => 'Some employees already have shifts assigned on selected days: ' . implode(', ', $workingConflicts),
                    'conflicts' => $workingConflicts,
                ];
            }

            $message = 'Shift assignment completed successfully.';
            if ($skipWorkingDays && $totalOffDays > 0) {
                $message = 'Off days were assigned. Existing weekday shifts were kept because of conflicts.';
            }

            return [
                'success' => true,
                'message' => $message,
                'total_assigned' => $totalAssigned,
                'total_off_days' => $totalOffDays,
            ];
        });
    }

    /**
     * Get data for the roster grid.
     */
    public function getGridData(int $year, int $month, int $weekIndex, ?string $filter = 'internal', bool $includeDeleted = false): array
    {
        // Calendar week range: always Monday -> Sunday
        // Week 1 starts from the Monday of the week that contains the first day of the month.
        $monthStart = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $firstWeekStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);

        $startDate = $firstWeekStart->copy()->addWeeks($weekIndex - 1)->startOfDay();
        $endDate = $startDate->copy()->addDays(6)->endOfDay();

        $employees = Employee::with('department')
            ->where('is_active', 1)
            ->shiftBasedWorkArrangement()
            ->orderBy('department_id')
            ->get();
        $outsourcedEmployees = OutsourcedEmployee::with('contractorCompany')
            ->whereNull('deleted_at')
            ->orderBy('full_name')
            ->get();

        $shiftEmployeeIds = $employees->pluck('id')->all();
        $outsourcedIds = $outsourcedEmployees->pluck('id')->all();

        // Build Department/Group List
        $departments = [];
        
        // 1. Add internal departments
        $deptIds = $employees->pluck('department_id')->unique();
        foreach ($deptIds as $did) {
            $emp = $employees->firstWhere('department_id', $did);
            if ($emp && $emp->department) {
                $departments[] = [
                    'id' => (int) $did,
                    'name' => $emp->department->name
                ];
            }
        }

        // 2. Add Contractor Companies as "virtual departments"
        // We use an offset (e.g. 1000000) to avoid ID collisions with internal departments
        $vendorIds = $outsourcedEmployees->pluck('contractor_company_id')->unique();
        foreach ($vendorIds as $vid) {
            $emp = $outsourcedEmployees->firstWhere('contractor_company_id', $vid);
            if ($emp && $emp->contractorCompany) {
                $departments[] = [
                    'id' => 1000000 + (int) $vid,
                    'name' => $emp->contractorCompany->third_party_name
                ];
            }
        }
        
        usort($departments, fn($a, $b) => strcmp($a['name'], $b['name']));

        // Build Employee Payload
        $empPayload = $employees->map(fn($e) => [
            'id' => 'employee:' . $e->id,
            'sourceType' => 'employee',
            'sourceId' => $e->id,
            'name' => $e->full_name,
            'departmentId' => (int) $e->department_id,
            'departmentName' => $e->department->name ?? 'Unassigned'
        ])->values();

        $outsourcedPayload = $outsourcedEmployees->map(fn($e) => [
            'id' => 'outsourced:' . $e->id,
            'sourceType' => 'outsourced',
            'sourceId' => $e->id,
            'name' => $e->full_name,
            'departmentId' => 1000000 + (int) $e->contractor_company_id,
            'departmentName' => $e->contractorCompany->third_party_name ?? 'Unassigned'
        ])->values();
        
        $empPayload = $empPayload->concat($outsourcedPayload)->values()->all();

        $employeeScope = function ($query) use ($shiftEmployeeIds, $outsourcedIds) {
            if ($shiftEmployeeIds !== [] || $outsourcedIds !== []) {
                $query->where(function ($q) use ($shiftEmployeeIds, $outsourcedIds) {
                    if ($shiftEmployeeIds !== []) {
                        $q->whereIn('employee_id', $shiftEmployeeIds);
                    }
                    if ($outsourcedIds !== []) {
                        $q->orWhereIn('outsourced_employee_id', $outsourcedIds);
                    }
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        };

        $entryRelations = [
            'shift',
            'createdBy:id,name',
            'updatedBy:id,name',
            'assignedBy:id,name',
            'deletedBy:id,name',
        ];

        $dateRange = [$startDate->toDateString(), $endDate->toDateString()];

        $entriesQuery = ShiftRosterEntry::with($entryRelations)
            ->whereBetween('roster_date', $dateRange);
        $employeeScope($entriesQuery);
        $entries = $entriesQuery->get();

        if ($includeDeleted) {
            $trashedQuery = ShiftRosterEntry::onlyTrashed()
                ->with($entryRelations)
                ->whereBetween('roster_date', $dateRange);
            $employeeScope($trashedQuery);
            $entries = $entries->merge($trashedQuery->get())->unique('id')->values();
        }

        $floorLabelMaps = [];
        foreach ($entries as $entry) {
            $employeeType = $entry->employee_id ? 'employee' : 'outsourced';
            $sourceId = (int) ($entry->employee_id ?: $entry->outsourced_employee_id);
            $lookupKey = $this->assigneeLookupKey($employeeType, $sourceId);

            if (! array_key_exists($lookupKey, $floorLabelMaps)) {
                $floorLabelMaps[$lookupKey] = collect($this->floorOptionsForAssignee($employeeType, $sourceId))
                    ->mapWithKeys(fn (array $option) => [$option['label'] => (int) $option['id']])
                    ->all();
            }
        }

        $shiftsOut = $entries->map(function ($entry) use ($floorLabelMaps) {
            $isOffDay = strtolower((string) $entry->status) === 'off';
            $shiftName = strtolower($entry->shift?->name ?? '');
            $isCustomTime = (bool) $entry->is_custom_time;
            $startTime = $isOffDay ? null : ($entry->start_time ?? $entry->shift?->start_time);
            $shiftType = $this->resolveShiftType($startTime, $isOffDay, $isCustomTime, $shiftName);

            $employeeType = $entry->employee_id ? 'employee' : 'outsourced';
            $sourceId = (int) ($entry->employee_id ?: $entry->outsourced_employee_id);
            $lookupKey = $this->assigneeLookupKey($employeeType, $sourceId);
            $floorLabel = $entry->floor;

            return [
                'rosterId' => $entry->id,
                'employeeId' => $entry->employee_id
                    ? ('employee:' . $entry->employee_id)
                    : ('outsourced:' . $entry->outsourced_employee_id),
                'employeeType' => $entry->employee_id ? 'employee' : 'outsourced',
                'sourceId' => $entry->employee_id ?: $entry->outsourced_employee_id,
                // Full date is used by the front-end to correctly align Mon->Sun columns.
                'rosterDate' => $entry->roster_date->toDateString(),
                // Kept for backward compatibility/fallbacks.
                'day' => (int) $entry->roster_date->format('d'),
                'shiftPlannerId' => $entry->shift_planner_id,
                'isCustomTime' => $isCustomTime,
                'shiftType' => $shiftType,
                'timeStart' => $isOffDay ? null : $this->formatShiftTime($entry->start_time ?? $entry->shift?->start_time),
                'timeEnd' => $isOffDay ? null : $this->formatShiftTime($entry->end_time ?? $entry->shift?->end_time),
                'floor' => $floorLabel,
                'location' => $entry->location_text,
                'notes' => $entry->notes,
                'sbuFloorId' => $floorLabel ? ($floorLabelMaps[$lookupKey][$floorLabel] ?? null) : null,
                'status' => $entry->status,
                'isOffDay' => $isOffDay,
                'isCompensatory' => $entry->is_compensatory_earned,
                'deletedAt' => $entry->deleted_at?->toDateTimeString(),
                'createdAt' => $entry->created_at?->toDateTimeString(),
                'updatedAt' => $entry->updated_by ? $entry->updated_at?->toDateTimeString() : null,
                'assignedAt' => $entry->assigned_by ? $entry->created_at?->toDateTimeString() : null,
                'createdByName' => $entry->createdBy?->name,
                'updatedByName' => $entry->updatedBy?->name,
                'assignedByName' => $entry->assignedBy?->name,
                'deletedByName' => $entry->deletedBy?->name,
            ];
        })->all();

        $virtualHolidays = $this->publicHolidayResolver->buildVirtualRosterHolidaysForGrid(
            $employees,
            $outsourcedEmployees,
            $startDate,
            $endDate
        );

        if ($virtualHolidays !== []) {
            $shiftsOut = array_merge($shiftsOut, $virtualHolidays);
        }

        return [
            'departments' => $departments,
            'employees' => $empPayload,
            'shifts' => $shiftsOut
        ];
    }

    private function formatShiftTime($value): string
    {
        if (!$value) return '00:00';
        return Carbon::parse($value)->format('H:i');
    }

    private function assigneeLookupKey(string $employeeType, int $employeeId): string
    {
        return $employeeType . ':' . $employeeId;
    }

    private function resolveAssigneeSbuId(string $employeeType, int $employeeId): ?int
    {
        if ($employeeType === 'outsourced') {
            $sbuId = OutsourcedEmployee::query()
                ->whereNull('deleted_at')
                ->whereKey($employeeId)
                ->value('sbu_id');
        } else {
            $sbuId = Employee::query()
                ->where('is_active', 1)
                ->shiftBasedWorkArrangement()
                ->whereKey($employeeId)
                ->value('sbu_id');
        }

        return $sbuId ? (int) $sbuId : null;
    }

    private function resolveAssigneeFloors(string $employeeType, int $employeeId)
    {
        $sbuId = $this->resolveAssigneeSbuId($employeeType, $employeeId);
        if (! $sbuId) {
            return collect();
        }

        return SbuFloor::query()
            ->where('sbu_id', $sbuId)
            ->where('is_active', true)
            ->orderBy('floor_number')
            ->orderBy('name')
            ->get();
    }

    private function mapFloorOptions($floors): array
    {
        return collect($floors)->map(fn (SbuFloor $floor) => [
            'id' => $floor->id,
            'name' => $floor->name,
            'floor_number' => $floor->floor_number,
            'label' => $this->formatFloorLabel($floor),
            'sbu_id' => $floor->sbu_id,
        ])->values()->all();
    }

    private function formatFloorLabel(SbuFloor $floor): string
    {
        $name = trim((string) $floor->name);
        $floorNumber = $floor->floor_number;

        if ($floorNumber !== null && $floorNumber !== '') {
            return trim($name . ' • ' . $floorNumber);
        }

        return $name;
    }

    private function applyFloorToPayload(array $data, array &$payload, ?string $existingFloor = null): void
    {
        if (! array_key_exists('sbu_floor_id', $data)) {
            $payload['floor'] = $existingFloor;

            return;
        }

        if ($data['sbu_floor_id'] === null) {
            $payload['floor'] = null;

            return;
        }

        $floor = SbuFloor::query()
            ->where('is_active', true)
            ->findOrFail((int) $data['sbu_floor_id']);

        $payload['floor'] = $this->formatFloorLabel($floor);
    }

    private function applyLocationToPayload(array $data, array &$payload, ?string $existingLocation = null): void
    {
        if (! array_key_exists('location_text', $data)) {
            $payload['location_text'] = $existingLocation;

            return;
        }

        $location = is_string($data['location_text']) ? trim($data['location_text']) : null;
        $payload['location_text'] = ($location === null || $location === '') ? null : $location;
    }

    private function applyNotesToPayload(array $data, array &$payload): void
    {
        if (! array_key_exists('notes', $data)) {
            return;
        }

        $notes = is_string($data['notes']) ? trim($data['notes']) : null;
        $payload['notes'] = ($notes === null || $notes === '') ? null : $notes;
    }

    /**
     * @param array<int, string> $refs
     * @return array<int, array{type:string,id:int}>
     */
    private function parseAssigneeRefs(array $refs): array
    {
        $parsed = [];
        foreach ($refs as $ref) {
            [$type, $id] = array_pad(explode(':', (string) $ref, 2), 2, null);
            $id = (int) $id;
            if (! in_array($type, ['employee', 'outsourced'], true) || ! $id) {
                continue;
            }
            $parsed[] = ['type' => $type, 'id' => $id];
        }
        return $parsed;
    }

    /**
     * @return array<int, string>
     */
    private function collectBulkConflicts(array $refs, string $startDate, string $endDate, array $days): array
    {
        $employeeIds = collect($refs)->where('type', 'employee')->pluck('id')->all();
        $outsourcedIds = collect($refs)->where('type', 'outsourced')->pluck('id')->all();

        $conflictingEntries = ShiftRosterEntry::query()
            ->with(['employee:id,full_name', 'outsourcedEmployee:id,full_name'])
            ->whereBetween('roster_date', [$startDate, $endDate])
            ->where(function ($query) use ($employeeIds, $outsourcedIds) {
                if ($employeeIds !== []) {
                    $query->whereIn('employee_id', $employeeIds);
                }
                if ($outsourcedIds !== []) {
                    $query->orWhereIn('outsourced_employee_id', $outsourcedIds);
                }
            })
            ->get()
            ->filter(function ($entry) use ($days) {
                return in_array(strtolower($entry->roster_date->format('l')), $days, true);
            });

        $names = [];
        foreach ($conflictingEntries as $entry) {
            $name = $entry->employee?->full_name ?? $entry->outsourcedEmployee?->full_name;
            if ($name) {
                $names[] = $name;
            }
        }

        return array_values(array_unique($names));
    }

    private function isCustomTimeRequest(array $data): bool
    {
        if (filter_var($data['is_custom_time'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        $hasTimes = ! empty($data['start_time']) && ! empty($data['end_time']);
        $hasShift = ! empty($data['shift_planner_id']);

        return $hasTimes && ! $hasShift;
    }

    private function buildEntryAssignment(array $data, ?ShiftRosterEntry $existing = null): array
    {
        if ($this->isCustomTimeRequest($data)) {
            return [
                'shift_planner_id' => null,
                'is_custom_time' => true,
                'start_time' => $this->formatShiftTime($data['start_time']),
                'end_time' => $this->formatShiftTime($data['end_time']),
            ];
        }

        $shiftId = $data['shift_planner_id'] ?? $existing?->shift_planner_id;
        if (! $shiftId) {
            throw new \InvalidArgumentException('Select a shift from the list or enable custom start and end time.');
        }

        $shift = ShiftPlanner::findOrFail((int) $shiftId);
        $hasTimeOverride = ! empty($data['start_time']) && ! empty($data['end_time']);

        return [
            'shift_planner_id' => $shift->id,
            'is_custom_time' => false,
            'start_time' => $hasTimeOverride
                ? $this->formatShiftTime($data['start_time'])
                : $this->formatShiftTime($shift->start_time),
            'end_time' => $hasTimeOverride
                ? $this->formatShiftTime($data['end_time'])
                : $this->formatShiftTime($shift->end_time),
        ];
    }

    private function resolveShiftType(?string $startTime, bool $isOffDay, bool $isCustomTime, string $shiftName = ''): string
    {
        if ($isOffDay) {
            return 'off';
        }

        if ($startTime) {
            $hour = (int) Carbon::parse($startTime)->format('H');
            if ($hour >= 4 && $hour < 12) {
                return 'morning';
            }
            if ($hour >= 12 && $hour < 18) {
                return 'evening';
            }
            if ($hour >= 18 || $hour < 4) {
                return 'night';
            }
        }

        if (! $isCustomTime && $shiftName !== '') {
            if (str_contains($shiftName, 'morning')) {
                return 'morning';
            }
            if (str_contains($shiftName, 'evening')) {
                return 'evening';
            }
            if (str_contains($shiftName, 'night')) {
                return 'night';
            }
        }

        return 'general';
    }

    private function upsertAssigneeShiftEntry(
        string $type,
        int $id,
        string $date,
        bool $overrideExisting,
        ?int $shiftPlannerId,
        bool $isCustomTime,
        ?string $startTime = null,
        ?string $endTime = null,
        array $placementData = []
    ): void {
        $userId = Auth::id();
        $shift = $shiftPlannerId ? ShiftPlanner::find($shiftPlannerId) : null;
        $basePayload = [
            'shift_planner_id' => $shiftPlannerId,
            'is_custom_time' => $isCustomTime,
            'start_time' => $startTime ?? ($shift ? $this->formatShiftTime($shift->start_time) : null),
            'end_time' => $endTime ?? ($shift ? $this->formatShiftTime($shift->end_time) : null),
            'status' => 'pending',
        ];
        $this->applyFloorToPayload($placementData, $basePayload);
        $this->applyLocationToPayload($placementData, $basePayload);
        $this->applyNotesToPayload($placementData, $basePayload);

        if ($type === 'outsourced') {
            $lookup = ['outsourced_employee_id' => $id, 'roster_date' => $date];
            $payload = ['employee_id' => null] + $basePayload;
        } else {
            $lookup = ['employee_id' => $id, 'roster_date' => $date];
            $payload = ['outsourced_employee_id' => null] + $basePayload;
        }

        if ($overrideExisting) {
            $this->saveDailyRosterEntry($lookup, $payload, $userId);

            return;
        }

        if (ShiftRosterEntry::query()->where($lookup)->exists()) {
            return;
        }

        $this->saveDailyRosterEntry($lookup, $payload, $userId);
    }

    private function upsertAssigneeOffEntry(string $type, int $id, string $date, ?int $shiftPlannerId, bool $overrideExisting): void
    {
        $userId = Auth::id();
        $basePayload = [
            'shift_planner_id' => $shiftPlannerId,
            'is_custom_time' => false,
            'start_time' => null,
            'end_time' => null,
            'floor' => null,
            'status' => 'off',
        ];

        if ($type === 'outsourced') {
            $lookup = ['outsourced_employee_id' => $id, 'roster_date' => $date];
            $payload = ['employee_id' => null] + $basePayload;
        } else {
            $lookup = ['employee_id' => $id, 'roster_date' => $date];
            $payload = ['outsourced_employee_id' => null] + $basePayload;
        }

        if ($overrideExisting) {
            $this->saveDailyRosterEntry($lookup, $payload, $userId);

            return;
        }

        if (ShiftRosterEntry::query()->where($lookup)->exists()) {
            return;
        }

        $this->saveDailyRosterEntry($lookup, $payload, $userId);
    }

    private function saveDailyRosterEntry(array $lookup, array $payload, ?int $userId): ShiftRosterEntry
    {
        $activeEntry = ShiftRosterEntry::query()->where($lookup)->first();

        if (! empty($lookup['employee_id'])) {
            $employee = Employee::query()->find((int) $lookup['employee_id']);
            $this->compensatoryLeaveAwardService->applyCompensatoryReasonToPayload(
                $payload,
                $activeEntry,
                $employee,
                (string) $payload['roster_date']
            );
        } else {
            $payload['compensatory_reason'] = null;
        }

        if ($activeEntry) {
            $before = $this->historyService->snapshot($activeEntry);
            $activeEntry->fill($payload);
            if ($userId) {
                $activeEntry->updated_by = $userId;
            }
            $activeEntry->save();
            $this->historyService->recordUpdated($activeEntry, $before, $userId);

            return $activeEntry;
        }

        $createPayload = $lookup + $payload;
        if ($userId) {
            $createPayload['created_by'] = $userId;
            $createPayload['assigned_by'] = $userId;
        }

        $entry = ShiftRosterEntry::query()->create($createPayload);
        $this->historyService->recordCreated($entry, $userId);

        return $entry;
    }

}
