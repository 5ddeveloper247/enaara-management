<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\OutsourcedEmployee;
use App\Models\SbuFloor;
use App\Models\ShiftPlanner;
use App\Models\ShiftRosterApprovalRequest;
use App\Models\ShiftRosterApprovalSegment;
use App\Models\ShiftRosterEntry;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ShiftRosterService
{
    public function __construct(
        private readonly ShiftRosterHistoryService $historyService,
        private readonly PublicHolidayResolver $publicHolidayResolver,
        private readonly CompensatoryLeaveAwardService $compensatoryLeaveAwardService,
    ) {
    }

    public function buildEntryAssignmentForApproval(array $data, ?ShiftRosterEntry $existing = null): array
    {
        return $this->buildEntryAssignment($data, $existing);
    }

    public function applyApprovedRosterEntry(array $lookup, array $payload, ?int $userId): ShiftRosterEntry
    {
        return $this->saveDailyRosterEntry($lookup, $payload, $userId);
    }

    public function syncCompensatoryTagsForEmployeeInRange(int $employeeId, string $startDate, string $endDate): void
    {
        $syncedWeekKeys = [];
        $this->compensatoryLeaveAwardService->syncFullWeekSundayTagsForEmployeeInRange(
            $employeeId,
            $startDate,
            $endDate,
            $syncedWeekKeys
        );
    }

    public function isDraftEntryOwnedByUser(ShiftRosterEntry $entry, ?int $userId): bool
    {
        return $this->viewerIsDraftApplier($entry, $userId);
    }

    public function resolveFloorLabelFromData(array $data): ?string
    {
        if (! array_key_exists('sbu_floor_id', $data) || $data['sbu_floor_id'] === null || $data['sbu_floor_id'] === '') {
            return null;
        }

        $floor = SbuFloor::query()
            ->where('is_active', true)
            ->find((int) $data['sbu_floor_id']);

        return $floor ? $this->formatFloorLabel($floor) : null;
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
     * Store a newly created shift roster entry as a draft pending approval.
     */
    public function store(array $data): ShiftRosterEntry
    {
        $employeeType = ($data['employee_type'] ?? 'employee') === 'outsourced' ? 'outsourced' : 'employee';
        $employeeId = (int) $data['employee_id'];
        $rosterDate = (string) $data['roster_date'];

        $this->assertNoExistingOffDay($employeeType, $employeeId, $rosterDate);

        [$lookup, $payload] = $this->buildDraftEntryPayload($data);

        return $this->saveDailyRosterEntry($lookup, $payload, Auth::id());
    }

    /**
     * Update an existing draft shift roster entry.
     */
    public function update(array $data, $id): ShiftRosterEntry
    {
        $entry = ShiftRosterEntry::query()->findOrFail((int) $id);
        $this->assertEntryEditable($entry);

        $wasPendingApproval = $this->isEntryInPendingApproval($entry);
        $pendingRequestId = $entry->shift_roster_approval_request_id;
        $pendingSegmentId = $entry->shift_roster_approval_segment_id;
        $markAsOff = filter_var($data['mark_as_off'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($markAsOff && strtolower((string) $entry->status) === 'off') {
            if ($wasPendingApproval && $pendingRequestId) {
                app(ShiftRosterApprovalService::class)->syncPendingRosterRequest(
                    (int) $pendingRequestId,
                    $pendingSegmentId ? (int) $pendingSegmentId : null
                );
            }

            return $entry->fresh(['shift', 'approvalRequest', 'approvalSegment']);
        }

        [$lookup, $payload] = $this->buildDraftEntryPayload($data, $entry);

        if ($wasPendingApproval) {
            $payload['shift_roster_approval_request_id'] = $pendingRequestId;
            $payload['shift_roster_approval_segment_id'] = $pendingSegmentId;
        } else {
            $payload['shift_roster_approval_request_id'] = null;
            $payload['shift_roster_approval_segment_id'] = null;
        }

        $saved = $this->saveDailyRosterEntry($lookup, $payload, Auth::id());

        if ($wasPendingApproval && $pendingRequestId) {
            app(ShiftRosterApprovalService::class)->syncPendingRosterRequest(
                (int) $pendingRequestId,
                $pendingSegmentId ? (int) $pendingSegmentId : null
            );
        }

        return $saved;
    }

    /**
     * Delete a draft shift roster entry.
     */
    public function destroy($id): void
    {
        $entry = ShiftRosterEntry::query()->findOrFail((int) $id);
        $this->assertEntryEditable($entry);

        $wasPendingApproval = $this->isEntryInPendingApproval($entry);
        $pendingRequestId = $entry->shift_roster_approval_request_id;
        $pendingSegmentId = $entry->shift_roster_approval_segment_id;

        $this->deleteApprovedRosterEntry(
            $entry->employee_id
                ? ['employee_id' => (int) $entry->employee_id, 'roster_date' => $entry->roster_date->toDateString()]
                : ['outsourced_employee_id' => (int) $entry->outsourced_employee_id, 'roster_date' => $entry->roster_date->toDateString()],
            Auth::id()
        );

        if ($wasPendingApproval && $pendingRequestId) {
            app(ShiftRosterApprovalService::class)->syncPendingRosterRequest(
                (int) $pendingRequestId,
                $pendingSegmentId ? (int) $pendingSegmentId : null
            );
        }
    }

    public function buildUpdateApprovalItem(ShiftRosterEntry $entry, array $data): array
    {
        $assignment = $this->buildEntryAssignment($data, $entry);
        $rosterDate = (string) ($data['roster_date'] ?? $entry->roster_date?->toDateString());
        $entryStatus = $this->resolveUpdatedEntryStatus($entry, $data);

        $placement = [];
        $this->applyFloorToPayload($data, $placement, $entry->floor);
        $this->applyLocationToPayload($data, $placement, $entry->location_text);
        $this->applyNotesToPayload($data, $placement);

        return [
            'roster_date' => $rosterDate,
            'entry_type' => 'shift',
            'shift_planner_id' => $assignment['shift_planner_id'],
            'is_custom_time' => $assignment['is_custom_time'],
            'start_time' => $assignment['start_time'],
            'end_time' => $assignment['end_time'],
            'floor' => $placement['floor'] ?? $entry->floor,
            'location_text' => $placement['location_text'] ?? $entry->location_text,
            'notes' => $placement['notes'] ?? $entry->notes,
            'entry_status' => $entryStatus,
        ];
    }

    public function buildDeleteApprovalItem(ShiftRosterEntry $entry): array
    {
        return [
            'roster_date' => $entry->roster_date?->toDateString(),
            'entry_type' => 'delete',
            'shift_planner_id' => $entry->shift_planner_id,
            'is_custom_time' => (bool) $entry->is_custom_time,
            'start_time' => $entry->start_time,
            'end_time' => $entry->end_time,
            'floor' => $entry->floor,
            'location_text' => $entry->location_text,
            'notes' => $entry->notes,
            'entry_status' => (string) $entry->status,
        ];
    }

    public function resolveApprovalLabelForEntry(ShiftRosterEntry $entry): string
    {
        if (strtolower((string) $entry->status) === 'off') {
            return 'Off day';
        }

        if ($entry->is_custom_time) {
            return 'Custom shift';
        }

        if ($entry->shift_planner_id) {
            return ShiftPlanner::query()->find($entry->shift_planner_id)?->name ?? 'Shift';
        }

        return 'Shift';
    }

    public function deleteApprovedRosterEntry(array $lookup, ?int $userId): void
    {
        $entry = ShiftRosterEntry::query()->where($lookup)->first();
        if (! $entry) {
            return;
        }

        $employeeId = $entry->employee_id;
        $rosterDate = $entry->roster_date;

        if ($userId) {
            $entry->deleted_by = $userId;
            $entry->save();
        }

        $this->historyService->recordDeleted($entry, $userId);
        $entry->delete();

        if ($employeeId) {
            $this->compensatoryLeaveAwardService->syncFullWeekSundayCompensatoryTag(
                (int) $employeeId,
                $rosterDate
            );
        }
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

                        if (! $overrideExisting && $this->assigneeEntryExists($ref['type'], $ref['id'], $date->toDateString())) {
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

                    if ($isOffDay) {
                        if (! $overrideExisting && $this->assigneeEntryExists($ref['type'], $ref['id'], $date->toDateString())) {
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
            }

            if ($skipWorkingDays && $totalAssigned === 0 && $totalOffDays === 0) {
                return [
                    'success' => false,
                    'message' => 'Some employees already have shifts assigned on selected days: ' . implode(', ', $workingConflicts),
                    'conflicts' => $workingConflicts,
                ];
            }

            if ($totalAssigned === 0 && $totalOffDays === 0) {
                return [
                    'success' => false,
                    'message' => 'No roster days were saved.',
                ];
            }

            $message = 'Shift roster saved as pending. Use Apply for Approval when ready.';
            if ($skipWorkingDays && $totalOffDays > 0) {
                $message = 'Off days saved as pending. Existing weekday shifts were kept because of conflicts.';
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
    public function getGridData(
        int $year,
        int $month,
        int $weekIndex,
        ?string $filter = 'internal',
        bool $includeDeleted = false,
        ?int $approvalRequestId = null
    ): array {
        // Calendar week range: always Monday -> Sunday
        // Week 1 starts from the Monday of the week that contains the first day of the month.
        $monthStart = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $firstWeekStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);

        $startDate = $firstWeekStart->copy()->addWeeks($weekIndex - 1)->startOfDay();
        $endDate = $startDate->copy()->addDays(6)->endOfDay();

        $viewerUserId = Auth::id();
        $viewerEmployeeId = Auth::user()?->employee_id ? (int) Auth::user()->employee_id : null;
        $approvalReviewScope = $this->resolveApprovalReviewScope(
            $approvalRequestId,
            $viewerUserId,
            $viewerEmployeeId
        );

        $employees = Employee::with(['department', 'assignedDesignation', 'role.roleLevel'])
            ->where('is_active', 1)
            ->shiftBasedWorkArrangement()
            ->get();
        $outsourcedEmployees = OutsourcedEmployee::with('contractorCompany')
            ->whereNull('deleted_at')
            ->orderBy('full_name')
            ->get();

        if ($approvalReviewScope) {
            $scopedEmployeeIds = $approvalReviewScope['employee_ids'];
            $scopedOutsourcedIds = $approvalReviewScope['outsourced_ids'];

            $employees = $employees->filter(
                fn (Employee $employee) => in_array((int) $employee->id, $scopedEmployeeIds, true)
            )->values();
            $outsourcedEmployees = $outsourcedEmployees->filter(
                fn (OutsourcedEmployee $outsourced) => in_array((int) $outsourced->id, $scopedOutsourcedIds, true)
            )->values();
        }

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
            'name' => $e->rosterDisplayName(),
            'employeeCode' => $e->employee_code ?? '',
            'designation' => trim((string) ($e->assignedDesignation?->name ?? $e->designation ?? '')),
            'roleLevel' => $e->role?->resolvedNumericLevel() ?? 999999,
            'departmentId' => (int) $e->department_id,
            'departmentName' => $e->department->name ?? 'Unassigned'
        ])->values();

        $outsourcedPayload = $outsourcedEmployees->map(fn($e) => [
            'id' => 'outsourced:' . $e->id,
            'sourceType' => 'outsourced',
            'sourceId' => $e->id,
            'name' => trim((string) ($e->full_name ?? '')),
            'employeeCode' => $e->biometric_id ? (string) $e->biometric_id : ('OSP-' . $e->id),
            'designation' => trim((string) ($e->job_role_trade ?? '')),
            'roleLevel' => 999999,
            'departmentId' => 1000000 + (int) $e->contractor_company_id,
            'departmentName' => $e->contractorCompany->third_party_name ?? 'Unassigned'
        ])->values();
        
        $empPayload = collect($empPayload)
            ->concat($outsourcedPayload)
            ->sortBy(fn ($emp) => sprintf(
                '%010d-%s',
                (int) ($emp['roleLevel'] ?? 999999),
                mb_strtolower((string) ($emp['name'] ?? ''))
            ))
            ->values()
            ->all();

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
            'approvalRequest',
            'approvalSegment',
            'createdBy:id,name',
            'updatedBy:id,name',
            'assignedBy:id,name',
            'deletedBy:id,name',
        ];

        $dateRange = [$startDate->toDateString(), $endDate->toDateString()];

        $entriesQuery = ShiftRosterEntry::with($entryRelations)
            ->whereBetween('roster_date', $dateRange);

        if ($approvalReviewScope) {
            $entriesQuery->where('shift_roster_approval_request_id', $approvalReviewScope['request_id']);
            if ($approvalReviewScope['segment_id']) {
                $entriesQuery->where('shift_roster_approval_segment_id', $approvalReviewScope['segment_id']);
            }
        } else {
            $employeeScope($entriesQuery);
        }

        $entries = $entriesQuery->get();

        $entries = $entries
            ->filter(fn (ShiftRosterEntry $entry) => $this->entryVisibleToViewer(
                $entry,
                $viewerUserId,
                $viewerEmployeeId
            ))
            ->values();

        if ($includeDeleted) {
            $trashedQuery = ShiftRosterEntry::onlyTrashed()
                ->with($entryRelations)
                ->whereBetween('roster_date', $dateRange);
            $employeeScope($trashedQuery);
            $entries = $entries
                ->merge(
                    $trashedQuery->get()
                        ->filter(fn (ShiftRosterEntry $entry) => $this->entryVisibleToViewer(
                            $entry,
                            $viewerUserId,
                            $viewerEmployeeId
                        ))
                )
                ->unique('id')
                ->values();
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

        $shiftsOut = $entries
            ->map(fn (ShiftRosterEntry $entry) => $this->mapEntryToGridShift(
                $entry,
                $floorLabelMaps,
                $viewerUserId,
                $viewerEmployeeId
            ))
            ->all();

        $virtualHolidays = $this->publicHolidayResolver->buildVirtualRosterHolidaysForGrid(
            $employees,
            $outsourcedEmployees,
            $startDate,
            $endDate
        );

        if ($virtualHolidays !== []) {
            $shiftsOut = array_merge($shiftsOut, $virtualHolidays);
        }

        // --- Add Virtual Leaves ---
        $leaveEntities = \App\Models\EmployeLeaveEntity::query()
            ->with('leaveRequest.leaveType')
            ->whereIn('employee_id', $shiftEmployeeIds)
            ->whereIn('status', [0, 1]) // 0: approved, 1: taken
            ->whereBetween('leave_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $virtualLeaves = $leaveEntities->map(function ($entity) {
            $dateString = $entity->leave_date->toDateString();
            $employeeKey = 'employee:' . $entity->employee_id;
            $leaveName = $entity->leaveRequest?->leaveType?->name ?? 'Leave';
            $isHalfDayLeave = (float) $entity->duration < 1.0
                || (bool) ($entity->leaveRequest?->is_half_day ?? false);
            $halfDaySession = $entity->half_day_session ?? $entity->leaveRequest?->half_day_session;

            return [
                'rosterId' => null,
                'employeeId' => $employeeKey,
                'employeeType' => 'employee',
                'sourceId' => $entity->employee_id,
                'rosterDate' => $dateString,
                'day' => (int) $entity->leave_date->format('d'),
                'shiftPlannerId' => null,
                'isCustomTime' => false,
                'shiftType' => $isHalfDayLeave ? 'half_leave' : 'leave',
                'timeStart' => null,
                'timeEnd' => null,
                'floor' => null,
                'location' => null,
                'notes' => null,
                'sbuFloorId' => null,
                'status' => 'leave',
                'isOffDay' => ! $isHalfDayLeave,
                'isPublicHoliday' => false,
                'isLeave' => true,
                'isHalfDayLeave' => $isHalfDayLeave,
                'leaveDuration' => (float) $entity->duration,
                'halfDaySession' => $halfDaySession,
                'leaveName' => $leaveName,
                'isCompensatory' => false,
                'deletedAt' => null,
                'createdAt' => null,
                'updatedAt' => null,
                'assignedAt' => null,
                'createdByName' => null,
                'updatedByName' => null,
                'assignedByName' => null,
                'deletedByName' => null,
            ];
        })->all();

        if ($virtualLeaves !== []) {
            $shiftsOut = array_merge($shiftsOut, $virtualLeaves);
        }

        $draftPendingQuery = ShiftRosterEntry::query()
            ->with(['approvalRequest', 'approvalSegment'])
            ->whereBetween('roster_date', $dateRange);
        $employeeScope($draftPendingQuery);
        $draftPendingCount = $draftPendingQuery
            ->whereNull('shift_roster_approval_request_id')
            ->whereIn('status', ['pending', 'off'])
            ->get()
            ->filter(fn (ShiftRosterEntry $entry) => $this->viewerIsDraftApplier($entry, $viewerUserId))
            ->count();

        return [
            'departments' => $departments,
            'employees' => $empPayload,
            'shifts' => $shiftsOut,
            'meta' => [
                'draftPendingCount' => $approvalReviewScope ? 0 : $draftPendingCount,
                'canApplyForApproval' => $approvalReviewScope ? false : $draftPendingCount > 0,
                'approvalReviewMode' => $approvalReviewScope !== null,
                'approvalRequestId' => $approvalReviewScope['request_id'] ?? null,
            ],
        ];
    }

    /**
     * @return array{request_id:int, segment_id:?int, employee_ids:array<int, int>, outsourced_ids:array<int, int>}|null
     */
    private function resolveApprovalReviewScope(
        ?int $approvalRequestId,
        ?int $viewerUserId,
        ?int $viewerEmployeeId
    ): ?array {
        if (! $approvalRequestId) {
            return null;
        }

        $request = ShiftRosterApprovalRequest::query()
            ->with('segments')
            ->find($approvalRequestId);

        if (! $request) {
            return null;
        }

        if (! $request->isPending()) {
            return null;
        }

        $this->assertCanAccessApprovalReview($request, $viewerUserId, $viewerEmployeeId);

        $entryQuery = ShiftRosterEntry::query()
            ->where('shift_roster_approval_request_id', $approvalRequestId);

        $segmentId = null;
        if ($request->request_type === 'roster') {
            $viewer = Auth::user();
            $segment = null;

            if ($viewerEmployeeId) {
                $segment = $request->segments
                    ->first(fn (ShiftRosterApprovalSegment $item) => (int) $item->approver_employee_id === (int) $viewerEmployeeId
                        && $item->isPending());
            }

            if ($segment) {
                $segmentId = (int) $segment->id;
                $entryQuery->where('shift_roster_approval_segment_id', $segmentId);
            } elseif (! $viewer?->isSystemAdminUser()) {
                throw ValidationException::withMessages([
                    'approval' => 'You are not authorized to review this roster request.',
                ]);
            }
        }

        $scopedEntries = $entryQuery->get(['employee_id', 'outsourced_employee_id']);

        return [
            'request_id' => $approvalRequestId,
            'segment_id' => $segmentId,
            'employee_ids' => $scopedEntries
                ->whereNotNull('employee_id')
                ->pluck('employee_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all(),
            'outsourced_ids' => $scopedEntries
                ->whereNotNull('outsourced_employee_id')
                ->pluck('outsourced_employee_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all(),
        ];
    }

    private function assertCanAccessApprovalReview(
        ShiftRosterApprovalRequest $request,
        ?int $viewerUserId,
        ?int $viewerEmployeeId
    ): void {
        $viewer = Auth::user();

        if ($viewer?->isSystemAdminUser()) {
            return;
        }

        if ($request->request_type === 'roster') {
            if ($viewerEmployeeId && $request->segments->contains(
                fn (ShiftRosterApprovalSegment $segment) => (int) $segment->approver_employee_id === (int) $viewerEmployeeId
            )) {
                return;
            }
        } elseif ($viewerEmployeeId && (int) $request->approver_employee_id === (int) $viewerEmployeeId) {
            return;
        }

        if ($viewerUserId && (int) $request->requested_by === (int) $viewerUserId) {
            return;
        }

        throw ValidationException::withMessages([
            'approval' => 'You are not authorized to review this roster request.',
        ]);
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
            ->with(['employee:id,first_name,middle_name,full_name,roster_display_middle_name', 'outsourcedEmployee:id,full_name'])
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
            $name = $entry->employee
                ? $entry->employee->rosterDisplayName()
                : trim((string) ($entry->outsourcedEmployee?->full_name ?? ''));
            if ($name !== '') {
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
            'shift_roster_approval_request_id' => null,
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

        $existingEntry = ShiftRosterEntry::query()->where($lookup)->first();
        if ($existingEntry && strtolower((string) $existingEntry->status) === 'off') {
            return;
        }

        if ($existingEntry) {
            $this->assertEntryEditable($existingEntry);
        }

        if ($overrideExisting || ! $existingEntry) {
            $this->saveDailyRosterEntry($lookup, $payload, $userId);

            return;
        }
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
            'shift_roster_approval_request_id' => null,
        ];

        if ($type === 'outsourced') {
            $lookup = ['outsourced_employee_id' => $id, 'roster_date' => $date];
            $payload = ['employee_id' => null] + $basePayload;
        } else {
            $lookup = ['employee_id' => $id, 'roster_date' => $date];
            $payload = ['outsourced_employee_id' => null] + $basePayload;
        }

        $existingEntry = ShiftRosterEntry::query()->where($lookup)->first();
        if ($existingEntry) {
            $this->assertEntryEditable($existingEntry);
        }

        if ($overrideExisting || ! $existingEntry) {
            $this->saveDailyRosterEntry($lookup, $payload, $userId);
        }
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
                (string) ($lookup['roster_date'] ?? $payload['roster_date'] ?? '')
            );
        } else {
            $payload['compensatory_reason'] = null;
        }

        if ($activeEntry) {
            if ($this->isGmApprovedEntry($activeEntry) && empty($activeEntry->published_snapshot)) {
                $payload['published_snapshot'] = $this->buildPublishedSnapshotFromEntry($activeEntry);
            }

            $before = $this->historyService->snapshot($activeEntry);
            $activeEntry->fill($payload);
            if ($userId) {
                $activeEntry->updated_by = $userId;
            }
            $activeEntry->save();
            $this->historyService->recordUpdated($activeEntry, $before, $userId);
            $savedEntry = $activeEntry;
        } else {
            $createPayload = $lookup + $payload;
            if ($userId) {
                $createPayload['created_by'] = $userId;
                $createPayload['assigned_by'] = $userId;
            }

            $savedEntry = ShiftRosterEntry::query()->create($createPayload);
            $this->historyService->recordCreated($savedEntry, $userId);
        }

        if (! empty($lookup['employee_id'])) {
            $this->compensatoryLeaveAwardService->syncFullWeekSundayCompensatoryTag(
                (int) $lookup['employee_id'],
                (string) ($savedEntry->roster_date ?? $lookup['roster_date'] ?? $payload['roster_date'] ?? now()->toDateString())
            );
        }

        return $savedEntry;
    }

    private function buildBulkShiftProposalItem(
        string $date,
        ?int $shiftPlannerId,
        bool $isCustomTime,
        ?string $startTime,
        ?string $endTime,
        array $placementData
    ): array {
        $shift = $shiftPlannerId ? ShiftPlanner::find($shiftPlannerId) : null;
        $item = [
            'roster_date' => $date,
            'entry_type' => 'shift',
            'shift_planner_id' => $shiftPlannerId,
            'is_custom_time' => $isCustomTime,
            'start_time' => $startTime ?? ($shift ? $this->formatShiftTime($shift->start_time) : null),
            'end_time' => $endTime ?? ($shift ? $this->formatShiftTime($shift->end_time) : null),
            'entry_status' => 'pending',
        ];

        $payload = [];
        $this->applyFloorToPayload($placementData, $payload);
        $this->applyLocationToPayload($placementData, $payload);
        $this->applyNotesToPayload($placementData, $payload);

        $item['floor'] = $payload['floor'] ?? null;
        $item['location_text'] = $payload['location_text'] ?? null;
        $item['notes'] = $payload['notes'] ?? null;

        return $item;
    }

    private function buildBulkOffProposalItem(string $date, ?int $shiftPlannerId): array
    {
        return [
            'roster_date' => $date,
            'entry_type' => 'off',
            'shift_planner_id' => $shiftPlannerId,
            'is_custom_time' => false,
            'start_time' => null,
            'end_time' => null,
            'floor' => null,
            'location_text' => null,
            'notes' => null,
            'entry_status' => 'off',
        ];
    }

    private function resolveBulkShiftLabel(?int $shiftPlannerId, bool $isCustomTime, array $data): string
    {
        if ($isCustomTime) {
            return 'Custom shift';
        }

        if ($shiftPlannerId) {
            return ShiftPlanner::query()->find($shiftPlannerId)?->name ?? 'Shift roster';
        }

        return 'Shift roster';
    }

    private function assigneeEntryExists(string $type, int $id, string $date): bool
    {
        $query = ShiftRosterEntry::query()->where('roster_date', $date);

        if ($type === 'outsourced') {
            return $query->where('outsourced_employee_id', $id)->exists();
        }

        return $query->where('employee_id', $id)->exists();
    }

    private function resolveUpdatedEntryStatus(ShiftRosterEntry $entry, array $data): string
    {
        if (strtolower((string) $entry->status) === 'off') {
            return 'pending';
        }

        if (isset($data['status'])) {
            return (int) $data['status'] === 1 ? 'pending' : 'cancelled';
        }

        return (string) $entry->status;
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    private function buildDraftEntryPayload(array $data, ?ShiftRosterEntry $existing = null): array
    {
        $employeeType = ($data['employee_type'] ?? 'employee') === 'outsourced' ? 'outsourced' : 'employee';
        $employeeId = (int) $data['employee_id'];
        $rosterDate = (string) ($data['roster_date'] ?? $existing?->roster_date?->toDateString());
        $markAsOff = filter_var($data['mark_as_off'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($employeeType === 'outsourced') {
            $lookup = ['outsourced_employee_id' => $employeeId, 'roster_date' => $rosterDate];
            $payload = ['employee_id' => null];
        } else {
            $lookup = ['employee_id' => $employeeId, 'roster_date' => $rosterDate];
            $payload = ['outsourced_employee_id' => null];
        }

        if ($markAsOff) {
            $payload += [
                'shift_planner_id' => null,
                'is_custom_time' => false,
                'start_time' => null,
                'end_time' => null,
                'floor' => null,
                'location_text' => null,
                'notes' => null,
                'status' => 'off',
                'shift_roster_approval_request_id' => null,
            ];

            return [$lookup, $payload];
        }

        $assignment = $this->buildEntryAssignment($data, $existing);
        $entryStatus = $existing
            ? $this->resolveUpdatedEntryStatus($existing, $data)
            : (((int) ($data['status'] ?? 1)) === 1 ? 'pending' : 'cancelled');

        $payload += [
            'shift_planner_id' => $assignment['shift_planner_id'],
            'is_custom_time' => $assignment['is_custom_time'],
            'start_time' => $assignment['start_time'],
            'end_time' => $assignment['end_time'],
            'status' => $entryStatus,
            'shift_roster_approval_request_id' => null,
        ];

        $this->applyFloorToPayload($data, $payload, $existing?->floor);
        $this->applyLocationToPayload($data, $payload, $existing?->location_text);
        $this->applyNotesToPayload($data, $payload);

        return [$lookup, $payload];
    }

    private function assertEntryEditable(ShiftRosterEntry $entry): void
    {
        if (! $this->isEntryInPendingApproval($entry)) {
            return;
        }

        if ($this->userCanEditPendingApprovalEntry($entry)) {
            return;
        }

        throw ValidationException::withMessages([
            'roster_date' => 'This roster is awaiting GM approval and cannot be changed.',
        ]);
    }

    private function userCanEditPendingApprovalEntry(ShiftRosterEntry $entry): bool
    {
        $viewer = Auth::user();
        $viewerUserId = Auth::id();

        if (! $viewerUserId) {
            return false;
        }

        if ($viewer?->isSystemAdminUser()) {
            return true;
        }

        $request = ShiftRosterApprovalRequest::query()->find($entry->shift_roster_approval_request_id);

        if ($request && (int) $request->requested_by === (int) $viewerUserId) {
            return true;
        }

        if ((int) $entry->created_by === (int) $viewerUserId) {
            return true;
        }

        if ($entry->assigned_by && (int) $entry->assigned_by === (int) $viewerUserId) {
            return true;
        }

        return $this->userIsPendingApprovalGmApprover($entry, $viewer);
    }

    private function userIsPendingApprovalGmApprover(ShiftRosterEntry $entry, $viewer): bool
    {
        $viewerEmployeeId = $viewer?->employee?->id;
        if (! $viewerEmployeeId) {
            return false;
        }

        $entry->loadMissing(['approvalRequest', 'approvalSegment']);

        if ($entry->approvalRequest?->request_type === 'roster') {
            return (int) $entry->approvalSegment?->approver_employee_id === (int) $viewerEmployeeId
                && $entry->approvalSegment?->approval_status === 'pending';
        }

        return (int) $entry->approvalRequest?->approver_employee_id === (int) $viewerEmployeeId
            && $entry->approvalRequest?->approval_status === 'pending';
    }

    private function isEntryInPendingApproval(ShiftRosterEntry $entry): bool
    {
        if (! $entry->shift_roster_approval_request_id) {
            return false;
        }

        $entry->loadMissing(['approvalRequest', 'approvalSegment']);

        if ($entry->approvalRequest?->request_type === 'roster') {
            return $entry->approvalSegment?->approval_status === 'pending'
                && $entry->approvalRequest?->approval_status === 'pending';
        }

        return $entry->approvalRequest?->approval_status === 'pending';
    }

    private function assertNoExistingOffDay(string $employeeType, int $employeeId, string $rosterDate): void
    {
        $query = ShiftRosterEntry::query()->where('roster_date', $rosterDate);

        if ($employeeType === 'outsourced') {
            $query->where('outsourced_employee_id', $employeeId);
        } else {
            $query->where('employee_id', $employeeId);
        }

        $existingEntry = $query->first();

        if ($existingEntry && strtolower((string) $existingEntry->status) === 'off') {
            throw ValidationException::withMessages([
                'roster_date' => 'This date is marked as off. Open the off day entry and convert it to assign a shift while keeping history.',
            ]);
        }
    }

    private function isDraftPendingEntry(ShiftRosterEntry $entry): bool
    {
        if ($entry->shift_roster_approval_request_id) {
            return false;
        }

        return in_array(strtolower((string) $entry->status), ['pending', 'off'], true);
    }

    private function isAwaitingGmApprovalEntry(ShiftRosterEntry $entry): bool
    {
        if (! $entry->shift_roster_approval_request_id) {
            return false;
        }

        if ($entry->approvalRequest?->request_type === 'roster') {
            return $entry->approvalSegment?->approval_status === 'pending';
        }

        return $entry->approvalRequest?->approval_status === 'pending';
    }

    private function isGmApprovedEntry(ShiftRosterEntry $entry): bool
    {
        if (! $entry->shift_roster_approval_request_id) {
            return false;
        }

        if ($entry->approvalRequest?->request_type === 'roster') {
            if ($entry->approvalSegment?->approval_status === 'approved') {
                return true;
            }

            return $entry->approvalRequest?->approval_status === 'approved';
        }

        return $entry->approvalRequest?->approval_status === 'approved';
    }

    private function resolveEntryApprovalStatus(ShiftRosterEntry $entry): ?string
    {
        if (! $entry->shift_roster_approval_request_id) {
            return null;
        }

        if ($entry->approvalRequest?->request_type === 'roster') {
            return $entry->approvalSegment?->approval_status
                ?? $entry->approvalRequest?->approval_status;
        }

        return $entry->approvalRequest?->approval_status;
    }

    public function restorePublishedSnapshot(ShiftRosterEntry $entry): ShiftRosterEntry
    {
        $snapshot = $entry->published_snapshot;
        if (! is_array($snapshot) || $snapshot === []) {
            return $entry;
        }

        $entry->fill([
            'shift_planner_id' => $snapshot['shift_planner_id'] ?? null,
            'is_custom_time' => (bool) ($snapshot['is_custom_time'] ?? false),
            'start_time' => $snapshot['start_time'] ?? null,
            'end_time' => $snapshot['end_time'] ?? null,
            'floor' => $snapshot['floor'] ?? null,
            'location_text' => $snapshot['location_text'] ?? null,
            'notes' => $snapshot['notes'] ?? null,
            'status' => $snapshot['status'] ?? 'pending',
            'shift_roster_approval_request_id' => $snapshot['shift_roster_approval_request_id'] ?? null,
            'shift_roster_approval_segment_id' => $snapshot['shift_roster_approval_segment_id'] ?? null,
            'is_compensatory_earned' => (bool) ($snapshot['is_compensatory_earned'] ?? false),
            'compensatory_reason' => $snapshot['compensatory_reason'] ?? null,
            'published_snapshot' => null,
        ]);
        $entry->save();

        return $entry->fresh();
    }

    public function clearPublishedSnapshotsForEntries(iterable $entryIds): void
    {
        ShiftRosterEntry::query()
            ->whereIn('id', collect($entryIds)->filter()->values()->all())
            ->update(['published_snapshot' => null]);
    }

    private function mapEntryToGridShift(
        ShiftRosterEntry $entry,
        array $floorLabelMaps,
        ?int $viewerUserId,
        ?int $viewerEmployeeId
    ): array {
        $usePublished = $this->shouldShowPublishedSnapshotToViewer($entry, $viewerUserId, $viewerEmployeeId);

        if ($this->shouldShowLiveOffDayToViewer($entry, $viewerUserId, $viewerEmployeeId)) {
            $usePublished = false;
        }

        $highlightPending = $this->shouldHighlightPendingChangeForViewer($entry, $viewerUserId, $viewerEmployeeId);
        $snapshot = $usePublished ? ($entry->published_snapshot ?? []) : [];

        $status = (string) ($usePublished ? ($snapshot['status'] ?? $entry->status) : $entry->status);
        $isOffDay = strtolower($status) === 'off';
        $shiftPlannerId = $usePublished
            ? ($snapshot['shift_planner_id'] ?? $entry->shift_planner_id)
            : $entry->shift_planner_id;
        $isCustomTime = $usePublished
            ? (bool) ($snapshot['is_custom_time'] ?? $entry->is_custom_time)
            : (bool) $entry->is_custom_time;
        $startTime = $usePublished ? ($snapshot['start_time'] ?? $entry->start_time) : $entry->start_time;
        $endTime = $usePublished ? ($snapshot['end_time'] ?? $entry->end_time) : $entry->end_time;
        $floorLabel = $usePublished ? ($snapshot['floor'] ?? $entry->floor) : $entry->floor;
        $locationText = $usePublished ? ($snapshot['location_text'] ?? $entry->location_text) : $entry->location_text;
        $notes = $usePublished ? ($snapshot['notes'] ?? $entry->notes) : $entry->notes;

        $shift = $entry->shift;
        if ($usePublished && $shiftPlannerId && (int) $shiftPlannerId !== (int) $entry->shift_planner_id) {
            $shift = ShiftPlanner::query()->find($shiftPlannerId);
        }

        $shiftName = strtolower($shift?->name ?? '');
        $resolvedStart = $isOffDay ? null : ($startTime ?? $shift?->start_time);
        $shiftType = $this->resolveShiftType($resolvedStart, $isOffDay, $isCustomTime, $shiftName);

        $employeeType = $entry->employee_id ? 'employee' : 'outsourced';
        $sourceId = (int) ($entry->employee_id ?: $entry->outsourced_employee_id);
        $lookupKey = $this->assigneeLookupKey($employeeType, $sourceId);

        return [
            'rosterId' => $entry->id,
            'employeeId' => $entry->employee_id
                ? ('employee:' . $entry->employee_id)
                : ('outsourced:' . $entry->outsourced_employee_id),
            'employeeType' => $employeeType,
            'sourceId' => $entry->employee_id ?: $entry->outsourced_employee_id,
            'rosterDate' => $entry->roster_date->toDateString(),
            'day' => (int) $entry->roster_date->format('d'),
            'shiftPlannerId' => $isOffDay ? null : $shiftPlannerId,
            'isCustomTime' => $isOffDay ? false : $isCustomTime,
            'shiftType' => $shiftType,
            'timeStart' => $isOffDay ? null : $this->formatShiftTime($startTime ?? $shift?->start_time),
            'timeEnd' => $isOffDay ? null : $this->formatShiftTime($endTime ?? $shift?->end_time),
            'floor' => $floorLabel,
            'location' => $locationText,
            'notes' => $notes,
            'sbuFloorId' => $floorLabel ? ($floorLabelMaps[$lookupKey][$floorLabel] ?? null) : null,
            'status' => $status,
            'approvalRequestId' => $usePublished
                ? ($snapshot['shift_roster_approval_request_id'] ?? null)
                : $entry->shift_roster_approval_request_id,
            'approvalStatus' => $usePublished ? 'approved' : $this->resolveEntryApprovalStatus($entry),
            'isDraftPending' => ! $usePublished && $this->isDraftPendingEntry($entry),
            'isAwaitingGmApproval' => ! $usePublished && $this->isAwaitingGmApprovalEntry($entry),
            'isGmApproved' => $usePublished || ($this->isGmApprovedEntry($entry) && ! $this->hasPendingPublishedChange($entry)),
            'isPendingChangeHighlight' => $highlightPending,
            'isOffDay' => $isOffDay,
            'isCompensatory' => $usePublished
                ? (bool) ($snapshot['is_compensatory_earned'] ?? false)
                : $entry->is_compensatory_earned,
            'deletedAt' => $entry->deleted_at?->toDateTimeString(),
            'createdAt' => $entry->created_at?->toDateTimeString(),
            'updatedAt' => $entry->updated_by ? $entry->updated_at?->toDateTimeString() : null,
            'assignedAt' => $entry->assigned_by ? $entry->created_at?->toDateTimeString() : null,
            'createdByName' => $entry->createdBy?->name,
            'updatedByName' => $entry->updatedBy?->name,
            'assignedByName' => $entry->assignedBy?->name,
            'deletedByName' => $entry->deletedBy?->name,
        ];
    }

    private function buildPublishedSnapshotFromEntry(ShiftRosterEntry $entry): array
    {
        return [
            'shift_planner_id' => $entry->shift_planner_id,
            'is_custom_time' => (bool) $entry->is_custom_time,
            'start_time' => $entry->start_time,
            'end_time' => $entry->end_time,
            'floor' => $entry->floor,
            'location_text' => $entry->location_text,
            'notes' => $entry->notes,
            'status' => (string) $entry->status,
            'shift_roster_approval_request_id' => $entry->shift_roster_approval_request_id,
            'shift_roster_approval_segment_id' => $entry->shift_roster_approval_segment_id,
            'is_compensatory_earned' => (bool) $entry->is_compensatory_earned,
            'compensatory_reason' => $entry->compensatory_reason,
        ];
    }

    private function hasPendingPublishedChange(ShiftRosterEntry $entry): bool
    {
        return is_array($entry->published_snapshot) && $entry->published_snapshot !== [];
    }

    private function shouldShowPublishedSnapshotToViewer(
        ShiftRosterEntry $entry,
        ?int $viewerUserId,
        ?int $viewerEmployeeId
    ): bool {
        if (! $this->hasPendingPublishedChange($entry)) {
            return false;
        }

        return ! $this->viewerSeesPendingChange($entry, $viewerUserId, $viewerEmployeeId);
    }

    private function shouldShowLiveOffDayToViewer(
        ShiftRosterEntry $entry,
        ?int $viewerUserId,
        ?int $viewerEmployeeId
    ): bool {
        if (strtolower((string) $entry->status) !== 'off') {
            return false;
        }

        if (! $this->hasPendingPublishedChange($entry)) {
            return true;
        }

        if ($this->viewerSeesPendingChange($entry, $viewerUserId, $viewerEmployeeId)) {
            return true;
        }

        return $this->userCanEditPendingApprovalEntry($entry);
    }

    private function shouldHighlightPendingChangeForViewer(
        ShiftRosterEntry $entry,
        ?int $viewerUserId,
        ?int $viewerEmployeeId
    ): bool {
        if (! $this->hasPendingPublishedChange($entry)) {
            return false;
        }

        return $this->viewerIsApplier($entry, $viewerUserId);
    }

    private function viewerIsApplier(ShiftRosterEntry $entry, ?int $viewerUserId): bool
    {
        if (! $viewerUserId) {
            return false;
        }

        if ($entry->shift_roster_approval_request_id) {
            return (int) $entry->approvalRequest?->requested_by === (int) $viewerUserId;
        }

        return $this->viewerIsDraftApplier($entry, $viewerUserId);
    }

    private function viewerIsDraftApplier(ShiftRosterEntry $entry, ?int $viewerUserId): bool
    {
        if (! $viewerUserId) {
            return false;
        }

        foreach (['created_by', 'assigned_by', 'updated_by'] as $field) {
            if ($entry->{$field} && (int) $entry->{$field} === (int) $viewerUserId) {
                return true;
            }
        }

        return false;
    }

    private function viewerSeesPendingChange(
        ShiftRosterEntry $entry,
        ?int $viewerUserId,
        ?int $viewerEmployeeId
    ): bool {
        if (! $viewerUserId) {
            return false;
        }

        $viewer = Auth::user();
        if ($viewer?->isSystemAdminUser()) {
            return true;
        }

        if ($entry->shift_roster_approval_request_id) {
            if ((int) $entry->approvalRequest?->requested_by === (int) $viewerUserId) {
                return true;
            }

            if ($viewerEmployeeId) {
                if ($entry->approvalRequest?->request_type === 'roster') {
                    if ((int) $entry->approvalSegment?->approver_employee_id === (int) $viewerEmployeeId
                        && $entry->approvalSegment?->approval_status === 'pending') {
                        return true;
                    }
                } elseif ((int) $entry->approvalRequest?->approver_employee_id === (int) $viewerEmployeeId
                    && $entry->approvalRequest?->approval_status === 'pending') {
                    return true;
                }
            }

            return false;
        }

        if ($this->isDraftPendingEntry($entry)) {
            return $this->viewerIsDraftApplier($entry, $viewerUserId);
        }

        return false;
    }

    private function isHistoricalRosterEntry(ShiftRosterEntry $entry): bool
    {
        if (! $entry->roster_date) {
            return false;
        }

        return Carbon::parse($entry->roster_date)->startOfDay()->lt(Carbon::today());
    }

    private function entryVisibleToViewer(
        ShiftRosterEntry $entry,
        ?int $viewerUserId,
        ?int $viewerEmployeeId
    ): bool {
        if (! $viewerUserId) {
            return false;
        }

        if ($this->isHistoricalRosterEntry($entry)) {
            return strtolower((string) $entry->status) !== 'cancelled';
        }

        $viewer = Auth::user();

        if ($this->isGmApprovedEntry($entry) && ! $this->hasPendingPublishedChange($entry)) {
            return true;
        }

        if ($this->hasPendingPublishedChange($entry)) {
            // Everyone keeps seeing this day; non-editors get the last approved snapshot in the grid.
            return true;
        }

        if ($entry->shift_roster_approval_request_id && $this->isEntryInPendingApproval($entry)) {
            if ((int) $entry->approvalRequest?->requested_by === (int) $viewerUserId) {
                return true;
            }

            if ($viewer && $this->userIsPendingApprovalGmApprover($entry, $viewer)) {
                return true;
            }

            return false;
        }

        if ($this->isDraftPendingEntry($entry)) {
            return $this->viewerIsDraftApplier($entry, $viewerUserId);
        }

        return false;
    }

}
