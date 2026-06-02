<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeLeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Services\leaverequestPrivatefunctions\AuthenticatedEmployeeRecords;
use App\Services\leaverequestPrivatefunctions\EmployeeLeaveQuotaRecords;
use App\Services\leaverequestPrivatefunctions\LeaveRequestSubmission;
use App\Services\leaverequestPrivatefunctions\LeaveRequestIndexData;
use App\Services\leaverequestPrivatefunctions\LeaveRequestStatusHandler;
use App\Services\leaverequestPrivatefunctions\LeaveRequestApplicationChecks;
use App\Services\leaverequestPrivatefunctions\LeaveRequestLeaveTypeFilter;

class LeaveRequestService
{
    public function __construct(
        protected AuthenticatedEmployeeRecords $authenticatedEmployeeRecords,
        protected EmployeeLeaveQuotaRecords $employeeLeaveQuotaRecords,
        protected LeaveRequestSubmission $leaveRequestSubmission,
        protected LeaveRequestStatusHandler $leaveRequestStatusHandler,
        protected LeaveRequestIndexData $leaveRequestIndexData,
        protected LeaveRequestApplicationChecks $leaveRequestApplicationChecks,
        protected LeaveRequestLeaveTypeFilter $leaveRequestLeaveTypeFilter,
    ) {}

    public function index()
    {
        return $this->leaveRequestIndexData->buildIndexView();
    }

    public function getMyLeavesLeaveTypes()
    {
        $employee = $this->authenticatedEmployeeRecords->resolveAuthenticatedEmployee();

        if ($employee === null) {
            return collect();
        }

        return $this->leaveRequestLeaveTypeFilter->filterForEmployee(
            $this->authenticatedEmployeeRecords->getLeaveTypesForEmployee($employee),
            $employee->id
        );
    }

    public function getPersonalQuotaSummary($employeeId)
    {
        $employee = Employee::find($employeeId);
        if (! $employee) {
            return [];
        }

        $leaveTypes = $this->authenticatedEmployeeRecords->getLeaveTypesForQuotaSummary($employee);

        return $this->employeeLeaveQuotaRecords->buildSummaryForEmployee(
            $employeeId,
            $leaveTypes
        );
    }

    public function filterPersonalQuotaForLeaveForm(array $personalQuota, int $employeeId): array
    {
        return $this->leaveRequestLeaveTypeFilter->filterQuotaSummary($personalQuota, $employeeId);
    }

    public function getPersonalLeaveHistory($employeeId)
    {
        $statusMap = [
            0 => 'pending',
            1 => 'recommended',
            2 => 'not_recommended',
            3 => 'approved',
            4 => 'rejected',
            5 => 'cancelled',
        ];

        $statusLabelMap = [
            0 => 'Pending Approval',
            1 => 'Recommended',
            2 => 'Not Recommended',
            3 => 'Approved',
            4 => 'Rejected',
            5 => 'Cancelled',
        ];

        $history = EmployeLeaveRequest::with('leaveType')
            ->where('from_employee_id', $employeeId)
            ->whereIn('action_type', [0, 2])
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get();

        $grouped = $history->groupBy(function (EmployeLeaveRequest $h) {
            $submittedMinute = Carbon::parse($h->created_at)->format('Y-m-d H:i');

            return (int) $h->leave_type_id.'|'.$h->start_date.'|'.$h->end_date.'|'.$submittedMinute;
        });

        return $grouped->map(function (Collection $rows) use ($statusMap, $statusLabelMap) {
            $representative = $rows->sortByDesc('id')->first();
            $aggregatedStatus = $this->aggregatePersonalLeaveStatuses($rows->pluck('status'));

            $startDate = Carbon::parse($representative->start_date);
            $endDate = Carbon::parse($representative->end_date);
            $today = Carbon::today();

            $category = 'past';
            if ($startDate->isFuture()) {
                $category = 'upcoming';
            } elseif ($today->between($startDate, $endDate)) {
                $category = 'active';
            }

            return [
                'id' => (int) $representative->id,
                'type' => $representative->leaveType ? strtolower(str_replace(' ', '-', $representative->leaveType->name)) : 'other',
                'typeLabel' => $representative->leaveType ? $representative->leaveType->name : 'Other',
                'startDate' => $representative->start_date,
                'endDate' => $representative->end_date,
                'days' => (float) $rows->max('duration'),
                'reason' => $representative->reason,
                'status' => $statusMap[$aggregatedStatus] ?? 'pending',
                'statusCode' => $aggregatedStatus,
                'statusLabel' => $statusLabelMap[$aggregatedStatus] ?? 'Pending',
                'category' => $category,
                // 0=pending (will delete permanently), 1=recommended, 2=not-recommended, 3=approved (all cancellable)
                'canCancel' => in_array($aggregatedStatus, [0, 1, 2, 3], true),
            ];
        })
            ->values()
            ->sortByDesc(function (array $item) {
                return $item['startDate'].' '.$item['endDate'].' '.str_pad((string) $item['id'], 10, '0', STR_PAD_LEFT);
            })
            ->values();
    }

    private function aggregatePersonalLeaveStatuses(Collection $statusValues): int
    {
        $statuses = $statusValues->map(fn ($s) => (int) $s)->unique();

        foreach ([4, 5, 3, 2, 1, 0] as $code) {
            if ($statuses->contains($code)) {
                return $code;
            }
        }

        return 0;
    }

    public function store(array $validated, Request $request): EmployeLeaveRequest
    {
        $fromEmployee = Employee::with('role')->findOrFail($validated['employee_id']);

        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['end_date'])->startOfDay();

        if ($endDate->lt($startDate)) {
            throw ValidationException::withMessages([
                'end_date' => 'End date must be after or equal to start date.',
            ]);
        }

        $duration = $this->calculateActualLeaveDuration($fromEmployee, $startDate, $endDate);

        if ($duration <= 0) {
            throw ValidationException::withMessages([
                'start_date' => 'The selected leave duration only consists of holidays, Sundays, or off days.',
            ]);
        }

        $leaveType = LeaveType::with('setting')->findOrFail((int) $validated['leave_type_id']);

        $this->leaveRequestApplicationChecks->assertEligibleForApplication(
            $fromEmployee,
            $leaveType,
            $startDate,
            $endDate,
            (float) $duration
        );

        $this->employeeLeaveQuotaRecords->assertCanRequestDays(
            $fromEmployee,
            (int) $validated['leave_type_id'],
            $startDate,
            (float) $duration
        );

        return $this->leaveRequestSubmission->create(
            $validated,
            $request,
            $fromEmployee,
            $startDate,
            $endDate,
            (float) $duration
        );
    }

    public function leaveTypesForEmployee(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        $employee = Employee::with('role')->findOrFail((int) $request->input('employee_id'));

        $leaveTypes = $this->leaveRequestLeaveTypeFilter
            ->filterForEmployee(
                $this->authenticatedEmployeeRecords->getLeaveTypesForQuotaSummary($employee),
                $employee->id
            )
            ->map(fn ($type) => $type->only(['id', 'name']))
            ->values();

        $quotaSummary = $this->leaveRequestLeaveTypeFilter->filterQuotaSummary(
            $this->getPersonalQuotaSummary($employee->id),
            $employee->id
        );

        return response()->json([
            'success' => true,
            'leaveTypes' => $leaveTypes,
            'quotaSummary' => $quotaSummary,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        return $this->leaveRequestStatusHandler->handle($request, (int) $id);
    }

    public function getActiveLeaveDates(Employee $employee, Carbon $startDate, Carbon $endDate): array
    {
        $activeDates = [];

        $holidayResolver = app(\App\Services\PublicHolidayResolver::class);
        $holidays = $holidayResolver->loadHolidaysForRange($startDate, $endDate);

        $rosterEntries = \App\Models\ShiftRosterEntry::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('roster_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $hasAnyRoster = $rosterEntries->isNotEmpty();

        $rostersByDate = $rosterEntries->keyBy(function ($item) {
            return $item->roster_date instanceof Carbon
                ? $item->roster_date->toDateString()
                : Carbon::parse($item->roster_date)->toDateString();
        });

        $current = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->startOfDay();

        while ($current->lte($end)) {
            $dateStr = $current->toDateString();

            // Check if public holiday applies
            $isPublicHoliday = $holidayResolver->resolveForAssigneeOnDate(
                $holidays,
                $employee->organization_id ? (int) $employee->organization_id : null,
                $employee->department_id ? (int) $employee->department_id : null,
                $employee->sbu_id ? (int) $employee->sbu_id : null,
                $dateStr
            ) !== null;

            if ($isPublicHoliday) {
                $current->addDay();
                continue;
            }

            // Check shift roster / weekly off
            if ($hasAnyRoster) {
                $roster = $rostersByDate->get($dateStr);
                if ($roster && strtolower(trim((string) $roster->status)) === 'off') {
                    $current->addDay();
                    continue;
                }
            } else {
                if ($current->isSunday()) {
                    $current->addDay();
                    continue;
                }
            }

            $activeDates[] = $dateStr;
            $current->addDay();
        }

        return $activeDates;
    }

    public function calculateActualLeaveDuration(Employee $employee, Carbon $startDate, Carbon $endDate): float
    {
        return (float) count($this->getActiveLeaveDates($employee, $startDate, $endDate));
    }
}
