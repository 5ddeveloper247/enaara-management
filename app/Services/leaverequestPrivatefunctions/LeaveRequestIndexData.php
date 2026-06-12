<?php

namespace App\Services\leaverequestPrivatefunctions;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeLeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class LeaveRequestIndexData
{
    private const MASTER_ACTION_TYPES = [0, 2];

    private const RECOMMENDATION_ACTION_TYPE = 1;

    private const FINAL_APPROVAL_ACTION_TYPE = 2;

    private const DEPARTMENT_HEAD_ROLE_LEVEL = 3;

    public const SCOPE_HUMAN_RESOURCE = 'human_resource';

    public const SCOPE_OTHER_DEPARTMENTS = 'other_departments';

    public function __construct(
        private EmployeeLeaveQuotaRecords $employeeLeaveQuotaRecords,
        private AuthenticatedEmployeeRecords $authenticatedEmployeeRecords,
        private LeaveRequestLeaveTypeFilter $leaveRequestLeaveTypeFilter,
        private LeaveRequestApproverResolver $leaveRequestApproverResolver,
    ) {}

    public function buildIndexView(): View
    {
        $currentUser = Auth::user();
        $currentEmployee = $currentUser?->employee;
        $showHrScopeTabs = $this->canShowHrScopeTabs($currentEmployee);
        $leaveScope = $this->resolveLeaveScope($showHrScopeTabs);

        $leaveRequests = $this->paginateLeaveRequests($currentEmployee, $leaveScope);
        $balanceLookup = $this->employeeLeaveQuotaRecords->buildBalanceLookupForRequests(
            $leaveRequests->getCollection()
        );

        $counts = $this->getDashboardCounts($currentEmployee, $leaveScope);
        $personalQuota = $this->buildPersonalQuota($currentEmployee);

        return view('admin.leave-requests.index', [
            'employeesGrouped' => $this->authenticatedEmployeeRecords->getEmployeesGroupedForLeaveApplication(),
            'leaveTypes' => $this->leaveRequestLeaveTypeFilter->excludeCompensatoryFromList(
                LeaveType::query()
                    ->with('setting:id,leave_type_id,short_leave_applicable')
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(['id', 'name', 'leave_condition'])
            ),
            'leaveRequests' => $leaveRequests,
            'mappedLeaveRequests' => $this->mapLeaveRequestsForTable(
                $leaveRequests->getCollection(),
                $currentUser,
                $balanceLookup,
                $leaveScope
            ),
            'pendingCount' => $counts['pending'],
            'approvedTodayCount' => $counts['approved_today'],
            'awayTodayCount' => $counts['away_today'],
            'overdueCount' => $counts['overdue'],
            'personalQuota' => $personalQuota,
            'showHrScopeTabs' => $showHrScopeTabs,
            'leaveScope' => $leaveScope,
        ]);
    }

    private function paginateLeaveRequests(?Employee $currentEmployee, string $leaveScope)
    {
        return EmployeLeaveRequest::with([
            'fromEmployee.department:id,name',
            'fromEmployee.role:id,parent_role_id,organization_id,department_id',
            'fromEmployee:id,full_name,department_id,organization_id,role_id',
            'toEmployee:id,full_name,department_id,organization_id,role_id',
            'toEmployee.role:id,name',
            'leaveType:id,name,annual_quota',
        ])
            ->when(
                $currentEmployee,
                fn ($query) => $this->applyLeaveScopeToQuery($query, $currentEmployee, $leaveScope),
                fn ($query) => $query->whereRaw('1 = 0')
            )
            ->latest('id')
            ->paginate(20)
            ->appends(['scope' => $leaveScope]);
    }

    private function mapLeaveRequestsForTable($requests, $currentUser, array $balanceLookup, string $leaveScope): array
    {
        $statusMap = $this->statusSlugMap();

        $currentEmployee = $currentUser?->employee;

        // Pre-fetch related requests to determine recommender and approver names
        $relatedRequests = \App\Models\EmployeLeaveRequest::with('toEmployee:id,full_name')
            ->whereIn('from_employee_id', $requests->pluck('from_employee_id'))
            ->whereIn('leave_type_id', $requests->pluck('leave_type_id'))
            ->get()
            ->groupBy(fn ($row) => $this->buildLeaveApplicationKey($row));

        return $requests->map(function ($request) use ($statusMap, $currentEmployee, $balanceLookup, $relatedRequests, $leaveScope) {
            $isAssignedApprover = $currentEmployee
                && (int) $request->to_employee_id === (int) $currentEmployee->id;

            $balanceKey = $this->employeeLeaveQuotaRecords->rowKey(
                (int) $request->from_employee_id,
                (int) $request->leave_type_id,
                (int) Carbon::parse($request->start_date)->year
            );

            $actionType = (int) $request->action_type;
            $statusCode = (int) $request->status;

            $canRecommend = $isAssignedApprover
                && $actionType === self::RECOMMENDATION_ACTION_TYPE
                && in_array($statusCode, [0, 1, 2], true);

            $canApprove = $isAssignedApprover
                && $actionType === self::FINAL_APPROVAL_ACTION_TYPE
                && $statusCode === 0;

            $requiresHrDelegationConfirm = false;
            $assignedApproverName = optional($request->toEmployee)->full_name;

            if ($leaveScope === self::SCOPE_OTHER_DEPARTMENTS && $this->canShowHrScopeTabs($currentEmployee)) {
                $canRecommend = false;
                $canApprove = $actionType === self::FINAL_APPROVAL_ACTION_TYPE
                    && $statusCode === 0;

                $requiresHrDelegationConfirm = $canApprove;
            }

            $siblings = $relatedRequests->get($this->buildLeaveApplicationKey($request), collect());

            $recommenderRow = $siblings
                ->where('action_type', self::RECOMMENDATION_ACTION_TYPE)
                ->sortByDesc('id')
                ->first();
            $approverRow = $siblings
                ->where('action_type', self::FINAL_APPROVAL_ACTION_TYPE)
                ->sortByDesc('id')
                ->first();

            $displayStatusCode = $this->aggregateApplicationStatus(
                $siblings->isNotEmpty() ? $siblings : collect([$request])
            );

            $currentLevelStr = match ($displayStatusCode) {
                1 => 'Recommended',
                2 => 'Not Recommended',
                3 => 'Approved',
                4 => 'Rejected',
                5 => 'Cancelled',
                default => 'Pending',
            };

            return [
                'id' => $request->id,
                'employeeName' => optional($request->fromEmployee)->full_name ?? 'Unknown',
                'employeeId' => 'EMP-' . str_pad($request->from_employee_id, 3, '0', STR_PAD_LEFT),
                'department' => optional(optional($request->fromEmployee)->department)->name ?? 'Unknown',
                'leaveType' => $request->leaveType
                    ? strtolower(str_replace(' ', '-', $request->leaveType->name))
                    : 'other',
                'leaveTypeLabel' => optional($request->leaveType)->name ?? 'Other',
                'startDate' => $request->start_date,
                'endDate' => $request->end_date,
                'days' => $request->duration,
                'isHalfDay' => (bool) $request->is_half_day,
                'halfDaySession' => $request->half_day_session,
                'isOutstationLeave' => (bool) $request->is_outstation_leave,
                'outstationDestination' => $request->outstation_destination,
                'outstationDestinationLabel' => app(LeaveRequestOutstationService::class)
                    ->destinationLabel($request->outstation_destination),
                'exemptDays' => (float) ($request->exempt_days ?? 0),
                'billableDays' => max(0.0, (float) $request->duration - (float) ($request->exempt_days ?? 0)),
                'reason' => $request->reason ?? '-',
                'status' => $statusMap[$displayStatusCode] ?? 'pending',
                'statusCode' => $displayStatusCode,
                'approvalLevel' => $currentLevelStr,
                'pendingSince' => $request->created_at ? $request->created_at->diffForHumans() : '-',
                'balance' => $balanceLookup[$balanceKey] ?? '0 / 0',
                'actionType' => $actionType,
                'isApprover' => $isAssignedApprover,
                'canApprove' => $canApprove,
                'canReject' => $canApprove,
                'canCancel' => $canApprove,
                'canRecommend' => $canRecommend,
                'canNotRecommend' => $canRecommend,
                'recommenderName' => $recommenderRow ? optional($recommenderRow->toEmployee)->full_name : null,
                'recommenderStatus' => $recommenderRow ? (int) $recommenderRow->status : null,
                'approverName' => $approverRow ? optional($approverRow->toEmployee)->full_name : null,
                'approverStatus' => $approverRow ? (int) $approverRow->status : null,
                'requiresHrDelegationConfirm' => $requiresHrDelegationConfirm,
                'assignedApproverName' => $assignedApproverName,
            ];
        })->values()->all();
    }

    private function getDashboardCounts(?Employee $currentEmployee, string $leaveScope): array
    {
        $row = EmployeLeaveRequest::query()
            ->when(
                $currentEmployee,
                fn ($query) => $this->applyLeaveScopeToQuery($query, $currentEmployee, $leaveScope),
                fn ($query) => $query->whereRaw('1 = 0')
            )
            ->whereIn('action_type', [0, 1, 2])
            ->selectRaw('SUM(CASE WHEN status IN (0, 1, 2) THEN 1 ELSE 0 END) as pending_count')
            ->selectRaw('SUM(CASE WHEN status = 3 AND updated_at >= ? THEN 1 ELSE 0 END) as approved_today_count', [now()->startOfDay()])
            ->selectRaw('SUM(CASE WHEN status = 3 AND start_date <= ? AND end_date >= ? THEN 1 ELSE 0 END) as away_today_count', [
                now()->toDateString(),
                now()->toDateString(),
            ])
            ->selectRaw('SUM(CASE WHEN status IN (0, 1, 2) AND created_at < ? THEN 1 ELSE 0 END) as overdue_count', [
                now()->subDays(2),
            ])
            ->first();

        return [
            'pending' => (int) ($row->pending_count ?? 0),
            'approved_today' => (int) ($row->approved_today_count ?? 0),
            'away_today' => (int) ($row->away_today_count ?? 0),
            'overdue' => (int) ($row->overdue_count ?? 0),
        ];
    }

    private function buildPersonalQuota(?Employee $employee): array
    {
        if ($employee === null) {
            return [];
        }

        $leaveTypes = $this->authenticatedEmployeeRecords->getLeaveTypesForQuotaSummary($employee);

        return $this->employeeLeaveQuotaRecords->buildSummaryForEmployee(
            $employee->id,
            $leaveTypes
        );
    }

    private function approvalLevelLabel(int $actionType): string
    {
        return match ($actionType) {
            self::FINAL_APPROVAL_ACTION_TYPE => 'Final Approval',
            self::RECOMMENDATION_ACTION_TYPE => 'Recommendation',
            default => '-',
        };
    }

    private function statusSlugMap(): array
    {
        return [
            0 => 'pending',
            1 => 'recommended',
            2 => 'not_recommended',
            3 => 'approved',
            4 => 'rejected',
            5 => 'cancelled',
        ];
    }

    private function buildLeaveApplicationKey(EmployeLeaveRequest $request): string
    {
        $submittedMinute = Carbon::parse($request->created_at)->format('Y-m-d H:i');

        return implode('_', [
            (int) $request->from_employee_id,
            (int) $request->leave_type_id,
            Carbon::parse($request->start_date)->toDateString(),
            Carbon::parse($request->end_date)->toDateString(),
            $submittedMinute,
        ]);
    }

    private function aggregateApplicationStatus(Collection $rows): int
    {
        $statuses = $rows->pluck('status')->map(fn ($status) => (int) $status)->unique();

        foreach ([4, 5, 3, 2, 1, 0] as $code) {
            if ($statuses->contains($code)) {
                return $code;
            }
        }

        return 0;
    }

    private function canShowHrScopeTabs(?Employee $employee): bool
    {
        if ($employee === null) {
            return false;
        }

        $employee->loadMissing('department');

        if (! $this->isHumanResourceDepartment($employee->department)) {
            return false;
        }

        return $this->leaveRequestApproverResolver->resolveEmployeeRoleLevel($employee) === self::DEPARTMENT_HEAD_ROLE_LEVEL;
    }

    private function resolveLeaveScope(bool $showHrScopeTabs): string
    {
        if (! $showHrScopeTabs) {
            return self::SCOPE_HUMAN_RESOURCE;
        }

        $scope = (string) request()->query('scope', self::SCOPE_HUMAN_RESOURCE);

        return in_array($scope, [self::SCOPE_HUMAN_RESOURCE, self::SCOPE_OTHER_DEPARTMENTS], true)
            ? $scope
            : self::SCOPE_HUMAN_RESOURCE;
    }

    private function applyLeaveScopeToQuery($query, Employee $currentEmployee, string $leaveScope): void
    {
        if (! $this->canShowHrScopeTabs($currentEmployee)) {
            $query->where('to_employee_id', $currentEmployee->id);

            return;
        }

        $viewerDepartmentId = $currentEmployee->department_id ? (int) $currentEmployee->department_id : null;

        if ($leaveScope === self::SCOPE_OTHER_DEPARTMENTS) {
            $otherDepartmentIds = $this->resolveOtherDepartmentIds($currentEmployee, $viewerDepartmentId);

            if ($otherDepartmentIds === []) {
                $query->whereRaw('1 = 0');

                return;
            }

            $query->where('action_type', self::FINAL_APPROVAL_ACTION_TYPE)
                ->whereHas(
                'fromEmployee',
                fn ($employeeQuery) => $employeeQuery->whereIn('department_id', $otherDepartmentIds)
            );

            return;
        }

        if (! $viewerDepartmentId) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where('to_employee_id', $currentEmployee->id)
            ->whereHas(
                'fromEmployee',
                fn ($employeeQuery) => $employeeQuery->where('department_id', $viewerDepartmentId)
            );
    }

    /**
     * @return array<int, int>
     */
    private function resolveOtherDepartmentIds(Employee $viewerEmployee, ?int $viewerDepartmentId): array
    {
        $sbuId = $viewerEmployee->sbu_id ? (int) $viewerEmployee->sbu_id : null;

        if (! $sbuId) {
            return [];
        }

        return Department::query()
            ->where('sbu_id', $sbuId)
            ->where('is_active', true)
            ->when(
                $viewerDepartmentId,
                fn ($query) => $query->where('id', '!=', $viewerDepartmentId)
            )
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    private function isHumanResourceDepartment(?Department $department): bool
    {
        if (! $department) {
            return false;
        }

        $normalized = strtolower(trim((string) $department->name));

        return in_array($normalized, ['human resource', 'human resources'], true);
    }
}
