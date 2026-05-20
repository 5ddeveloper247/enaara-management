<?php

namespace App\Services\leaverequestPrivatefunctions;

use App\Models\Employee;
use App\Models\EmployeLeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $isSuperAdmin = $this->userIsSuperAdmin((int) $currentUser->id);

        $leaveRequests = $this->paginateLeaveRequests($currentEmployee, $isSuperAdmin);
        $balanceLookup = $this->employeeLeaveQuotaRecords->buildBalanceLookupForRequests(
            $leaveRequests->getCollection()
        );

        $counts = $this->getDashboardCounts();
        $personalQuota = $this->buildPersonalQuota($currentEmployee);

        return view('admin.leave-requests.index', [
            'employees' => Employee::query()
                ->where('is_active', true)
                ->orderBy('full_name')
                ->get(['id', 'full_name']),
            'leaveTypes' => LeaveType::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'leaveRequests' => $leaveRequests,
            'mappedLeaveRequests' => $this->mapLeaveRequestsForTable(
                $leaveRequests->getCollection(),
                $currentUser,
                $isSuperAdmin,
                $balanceLookup
            ),
            'pendingCount' => $counts['pending'],
            'approvedTodayCount' => $counts['approved_today'],
            'awayTodayCount' => $counts['away_today'],
            'overdueCount' => $counts['overdue'],
            'personalQuota' => $personalQuota,
        ]);
    }

    private function paginateLeaveRequests(?Employee $currentEmployee, bool $isSuperAdmin)
    {
        return EmployeLeaveRequest::with([
            'fromEmployee.department:id,name',
            'fromEmployee.role:id,parent_role_id,organization_id,department_id',
            'fromEmployee:id,full_name,department_id,organization_id,role_id',
            'toEmployee:id,full_name,department_id,organization_id,role_id',
            'leaveType:id,name,annual_quota',
        ])
            ->when(! $isSuperAdmin && $currentEmployee, function ($query) use ($currentEmployee) {
                $query->where('to_employee_id', $currentEmployee->id);
            })
            ->latest('id')
            ->paginate(20);
    }

    private function mapLeaveRequestsForTable($requests, $currentUser, bool $isSuperAdmin, array $balanceLookup): array
    {
        $statusMap = $this->statusSlugMap();

        return $requests->map(function ($request) use ($statusMap, $currentUser, $isSuperAdmin, $balanceLookup) {
            $isApprover = $currentUser && $currentUser->id === $request->to_user_id;

            $balanceKey = $this->employeeLeaveQuotaRecords->rowKey(
                (int) $request->from_employee_id,
                (int) $request->leave_type_id,
                (int) Carbon::parse($request->start_date)->year
            );

            $canRecommend = $isApprover && (int) $request->action_type === self::RECOMMENDATION_ACTION_TYPE;
            $canApprove = $isApprover && (int) $request->action_type === self::FINAL_APPROVAL_ACTION_TYPE;

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
                'isApprover' => $isApprover,
                'isSuperAdmin' => $isSuperAdmin,
                'canApprove' => $canApprove || $isSuperAdmin,
                'canReject' => $canApprove || $isSuperAdmin,
                'canCancel' => $canApprove || $isSuperAdmin,
                'canRecommend' => $canRecommend || $isSuperAdmin,
                'canNotRecommend' => $canRecommend || $isSuperAdmin,
            ];
        })->values()->all();
    }

    private function getDashboardCounts(): array
    {
        $row = EmployeLeaveRequest::query()
            ->whereIn('action_type', self::MASTER_ACTION_TYPES)
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

    private function userIsSuperAdmin(int $userId): bool
    {
        return DB::table('user_roles')
            ->where('user_id', $userId)
            ->where('role_id', 1)
            ->exists();
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
