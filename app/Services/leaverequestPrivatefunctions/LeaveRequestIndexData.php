<?php

namespace App\Services\leaverequestPrivatefunctions;

use App\Models\Employee;
use App\Models\EmployeLeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class LeaveRequestIndexData
{
    private const MASTER_ACTION_TYPES = [0, 2];

    private const RECOMMENDATION_ACTION_TYPE = 1;

    private const FINAL_APPROVAL_ACTION_TYPE = 2;

    public function __construct(
        private EmployeeLeaveQuotaRecords $employeeLeaveQuotaRecords,
        private AuthenticatedEmployeeRecords $authenticatedEmployeeRecords,
    ) {}

    public function buildIndexView(): View
    {
        $currentUser = Auth::user();
        $currentEmployee = $currentUser?->employee;

        $leaveRequests = $this->paginateLeaveRequests($currentEmployee);
        $balanceLookup = $this->employeeLeaveQuotaRecords->buildBalanceLookupForRequests(
            $leaveRequests->getCollection()
        );

        $counts = $this->getDashboardCounts($currentEmployee);
        $personalQuota = $this->buildPersonalQuota($currentEmployee);

        return view('admin.leave-requests.index', [
            'employees' => Employee::query()
                ->where('is_active', true)
                ->orderBy('full_name')
                ->get(['id', 'full_name', 'employee_code']),
            'leaveTypes' => LeaveType::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'leaveRequests' => $leaveRequests,
            'mappedLeaveRequests' => $this->mapLeaveRequestsForTable(
                $leaveRequests->getCollection(),
                $currentUser,
                $balanceLookup
            ),
            'pendingCount' => $counts['pending'],
            'approvedTodayCount' => $counts['approved_today'],
            'awayTodayCount' => $counts['away_today'],
            'overdueCount' => $counts['overdue'],
            'personalQuota' => $personalQuota,
        ]);
    }

    private function paginateLeaveRequests(?Employee $currentEmployee)
    {
        return EmployeLeaveRequest::with([
            'fromEmployee.department:id,name',
            'fromEmployee.role:id,parent_role_id,organization_id,department_id',
            'fromEmployee:id,full_name,department_id,organization_id,role_id',
            'toEmployee:id,full_name,department_id,organization_id,role_id',
            'leaveType:id,name,annual_quota',
        ])
            ->when(
                $currentEmployee,
                fn ($query) => $query->where('to_employee_id', $currentEmployee->id),
                fn ($query) => $query->whereRaw('1 = 0')
            )
            ->latest('id')
            ->paginate(20);
    }

    private function mapLeaveRequestsForTable($requests, $currentUser, array $balanceLookup): array
    {
        $statusMap = $this->statusSlugMap();

        $currentEmployee = $currentUser?->employee;

        return $requests->map(function ($request) use ($statusMap, $currentEmployee, $balanceLookup) {
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
                'reason' => $request->reason ?? '-',
                'status' => $statusMap[$request->status] ?? 'pending',
                'statusCode' => $request->status,
                'approvalLevel' => $this->approvalLevelLabel((int) $request->action_type),
                'pendingSince' => $request->created_at ? $request->created_at->diffForHumans() : '-',
                'balance' => $balanceLookup[$balanceKey] ?? '0 / 0',
                'actionType' => $actionType,
                'isApprover' => $isAssignedApprover,
                'canApprove' => $canApprove,
                'canReject' => $canApprove,
                'canCancel' => $canApprove,
                'canRecommend' => $canRecommend,
                'canNotRecommend' => $canRecommend,
            ];
        })->values()->all();
    }

    private function getDashboardCounts(?Employee $currentEmployee): array
    {
        $row = EmployeLeaveRequest::query()
            ->when(
                $currentEmployee,
                fn ($query) => $query->where('to_employee_id', $currentEmployee->id),
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
}
