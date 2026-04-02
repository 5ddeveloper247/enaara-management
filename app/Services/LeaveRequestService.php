<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeLeaveRequest;
use App\Models\LeaveType;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Notifications\LeaveApprovalNotification;
use App\Notifications\LeaveApprovedToHodNotification;
use App\Services\AuditTrailService;

class LeaveRequestService
{
    protected $auditTrailService;

    public function __construct(AuditTrailService $auditTrailService)
    {
        $this->auditTrailService = $auditTrailService;
    }

    public function index()
    {
        $employees = Employee::where('is_active', true)->orderBy('full_name')->get();
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();

        $currentUser = Auth::user();
        $currentEmployee = $currentUser?->employee;

        $statusMap = [
            0 => 'pending',
            1 => 'recommended',
            2 => 'not_recommended',
            3 => 'approved',
            4 => 'rejected',
            5 => 'cancelled',
        ];

        $isSuperAdmin = \DB::table('user_roles')
            ->where('user_id', $currentUser->id)
            ->where('role_id', 1)
            ->exists();

        $leaveRequests = EmployeLeaveRequest::with([
            'fromEmployee.department:id,name',
            'fromEmployee.role:id,parent_role_id,organization_id,department_id',
            'fromEmployee:id,full_name,department_id,organization_id,role_id',
            'toEmployee:id,full_name,department_id,organization_id,role_id',
            'leaveType:id,name',
        ])
            ->when(!$isSuperAdmin && $currentEmployee, function ($query) use ($currentEmployee) {
                $query->where(function ($q) use ($currentEmployee) {
                    $q->where('to_employee_id', $currentEmployee->id);
                });
            })
            ->latest('id')
            ->paginate(20);

        $mappedLeaveRequests = $leaveRequests->getCollection()->map(function ($request) use ($statusMap, $currentUser, $isSuperAdmin) {
            $isApprover = ($currentUser && $currentUser->id === $request->to_user_id);

            $year = Carbon::parse($request->start_date)->year;

            $quotaRecord = \App\Models\EmployeeLeaveQuota::where('employee_id', $request->from_employee_id)
                ->where('leave_type_id', $request->leave_type_id)
                ->where('year', $year)
                ->first();

            $maxAllowed = (float) ($quotaRecord ? $quotaRecord->adjusted_quota : (optional($request->leaveType)->annual_quota ?? 0));
            $claimed = $quotaRecord ? (float) $quotaRecord->used : 0;
            $remaining = max(0, $maxAllowed - $claimed);

            $canRecommend = $isApprover && (int) $request->action_type === 1;
            $canApprove = $isApprover && (int) $request->action_type === 2;

            return [
                'id' => $request->id,
                'employeeName' => optional($request->fromEmployee)->full_name ?? 'Unknown',
                'employeeId' => 'EMP-' . str_pad($request->from_employee_id, 3, '0', STR_PAD_LEFT),
                'department' => optional(optional($request->fromEmployee)->department)->name ?? 'Unknown',
                'leaveType' => $request->leaveType ? strtolower(str_replace(' ', '-', $request->leaveType->name)) : 'other',
                'leaveTypeLabel' => optional($request->leaveType)->name ?? 'Other',
                'startDate' => $request->start_date,
                'endDate' => $request->end_date,
                'days' => $request->duration,
                'reason' => $request->reason ?? '-',
                'status' => $statusMap[$request->status] ?? 'pending',
                'statusCode' => $request->status,
                'approvalLevel' => (int) $request->action_type === 2
                    ? 'Final Approval'
                    : ((int) $request->action_type === 1 ? 'Recommendation' : '-'),
                'pendingSince' => $request->created_at ? $request->created_at->diffForHumans() : '-',
                'balance' => $remaining . ' / ' . $maxAllowed,
                'isApprover' => $isApprover,
                'isSuperAdmin' => $isSuperAdmin,
                'canApprove' => $canApprove || $isSuperAdmin,
                'canReject' => $canApprove || $isSuperAdmin,
                'canCancel' => $canApprove || $isSuperAdmin,
                'canRecommend' => $canRecommend || $isSuperAdmin,
                'canNotRecommend' => $canRecommend || $isSuperAdmin,
            ];
        })->values()->all();

        // Counts should be based on master/final rows only
        $pendingCount = EmployeLeaveRequest::whereIn('status', [0, 1, 2])
            ->whereIn('action_type', [0, 2])
            ->count();

        $approvedTodayCount = EmployeLeaveRequest::where('status', 3)
            ->whereIn('action_type', [0, 2])
            ->where('updated_at', '>=', now()->startOfDay())
            ->count();

        $awayTodayCount = EmployeLeaveRequest::where('status', 3)
            ->whereIn('action_type', [0, 2])
            ->where('start_date', '<=', now()->toDateString())
            ->where('end_date', '>=', now()->toDateString())
            ->count();

        $overdueCount = EmployeLeaveRequest::whereIn('status', [0, 1, 2])
            ->whereIn('action_type', [0, 2])
            ->where('created_at', '<', now()->subDays(2))
            ->count();

        $personalQuota = $currentEmployee ? $this->getPersonalQuotaSummary($currentEmployee->id) : [];

        return view('admin.leave-requests.index', [
            'employees' => $employees,
            'leaveTypes' => $leaveTypes,
            'leaveRequests' => $leaveRequests,
            'mappedLeaveRequests' => $mappedLeaveRequests,
            'pendingCount' => $pendingCount,
            'approvedTodayCount' => $approvedTodayCount,
            'awayTodayCount' => $awayTodayCount,
            'overdueCount' => $overdueCount,
            'personalQuota' => $personalQuota,
        ]);
    }

    public function getPersonalQuotaSummary($employeeId)
    {
        $employee = Employee::find($employeeId);
        $year = now()->year;

        $organizationId = $employee->organization_id;
        $departmentId = $employee->department_id;

        $leaveTypes = LeaveType::where('is_active', true)
            ->when($organizationId, fn($q) => $q->where(function ($qq) use ($organizationId) {
                $qq->whereNull('organization_id')->orWhere('organization_id', $organizationId);
            }))
            ->when($departmentId, fn($q) => $q->where(function ($qq) use ($departmentId) {
                $qq->whereNull('department_id')->orWhere('department_id', $departmentId);
            }))
            ->get();

        $summary = [];
        foreach ($leaveTypes as $type) {
            $quotaRecord = \App\Models\EmployeeLeaveQuota::where('employee_id', $employeeId)
                ->where('leave_type_id', $type->id)
                ->where('year', $year)
                ->first();

            $maxAllowed = (float) ($quotaRecord ? $quotaRecord->adjusted_quota : $type->annual_quota);
            $claimed = $quotaRecord ? (float) $quotaRecord->used : 0;

            $summary[] = [
                'id' => $type->id,
                'type' => $type->name,
                'total' => $maxAllowed,
                'used' => $claimed,
                'remaining' => max(0, $maxAllowed - $claimed),
                'percentage' => $maxAllowed > 0 ? min(100, round(($claimed / $maxAllowed) * 100)) : 0,
            ];
        }

        return $summary;
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
            ->whereIn('action_type', [0, 2]) // Only master/final rows
            ->latest('start_date')
            ->get();

        return $history->map(function ($h) use ($statusMap, $statusLabelMap) {
            $startDate = Carbon::parse($h->start_date);
            $endDate = Carbon::parse($h->end_date);
            $today = Carbon::today();

            $category = 'past';
            if ($startDate->isFuture()) {
                $category = 'upcoming';
            } elseif ($today->between($startDate, $endDate)) {
                $category = 'active';
            }

            return [
                'id' => $h->id,
                'type' => $h->leaveType ? strtolower(str_replace(' ', '-', $h->leaveType->name)) : 'other',
                'typeLabel' => $h->leaveType ? $h->leaveType->name : 'Other',
                'startDate' => $h->start_date,
                'endDate' => $h->end_date,
                'days' => $h->duration,
                'reason' => $h->reason,
                'status' => $statusMap[$h->status] ?? 'pending',
                'statusLabel' => $statusLabelMap[$h->status] ?? 'Pending',
                'category' => $category,
            ];
        });
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

        $duration = $startDate->diffInDays($endDate) + 1;

        // Date conflict check
        $hasConflict = EmployeLeaveRequest::where('from_employee_id', $fromEmployee->id)
            ->whereIn('status', [0, 1, 3])
            ->whereIn('action_type', [0, 2]) // Only master/final rows
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->where('start_date', '<=', $endDate->toDateString())
                        ->where('end_date', '>=', $startDate->toDateString());
                });
            })
            ->exists();

        if ($hasConflict) {
            throw ValidationException::withMessages([
                'start_date' => 'You already have a pending or approved leave request during this date range.',
            ]);
        }

        // Quota check
        $year = $startDate->year;
        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);

        $quotaRecord = \App\Models\EmployeeLeaveQuota::where('employee_id', $fromEmployee->id)
            ->where('leave_type_id', $validated['leave_type_id'])
            ->where('year', $year)
            ->first();

        $maxAllowed = (float) ($quotaRecord ? $quotaRecord->adjusted_quota : ($leaveType->annual_quota ?? 0));
        $alreadyClaimed = $quotaRecord ? (float) $quotaRecord->used : 0;

        $pendingRequested = (float) EmployeLeaveRequest::where('from_employee_id', $fromEmployee->id)
            ->where('leave_type_id', $validated['leave_type_id'])
            ->whereYear('start_date', $year)
            ->whereIn('status', [0, 1])
            ->whereIn('action_type', [0, 2]) // only master/final rows
            ->sum('duration');

        $alreadyClaimed += $pendingRequested;

        if (($alreadyClaimed + $duration) > $maxAllowed) {
            $remaining = max(0, $maxAllowed - $alreadyClaimed);
            throw ValidationException::withMessages([
                'leave_type_id' => "Insufficient leave balance. You have {$remaining} day(s) remaining for this leave type in {$year}, but you are requesting {$duration} day(s).",
            ]);
        }

        $medicalReportPath = null;

        if ($request->hasFile('medical_report')) {
            $medicalReportPath = $request->file('medical_report')
                ->store('leave-request/medical-reports', 'public');
        }

        $lineManagers = $this->resolveParentApprovers($fromEmployee);
        $hodUser = $this->resolveHeadOfDepartment($fromEmployee);
        $hodEmployee = $hodUser?->employee;

        $createdRequests = [];

        // Create separate recommendation row for each line manager
        foreach ($lineManagers as $manager) {
            $managerUser = User::where('employee_id', $manager->id)->first();

            $leaveRequest = EmployeLeaveRequest::create([
                'from_employee_id' => $fromEmployee->id,
                'to_employee_id' => $manager->id,
                'from_user_id' => Auth::id(),
                'to_user_id' => $managerUser?->id,
                'department_id' => $fromEmployee->department_id,
                'leave_type_id' => $validated['leave_type_id'],
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'duration' => $duration,
                'reason' => $validated['reason'] ?? null,
                'medical_report' => $medicalReportPath,
                'action_type' => 1,
                'status' => 0,
            ]);

            $leaveRequest->load([
                'fromEmployee:id,full_name',
                'toEmployee:id,full_name',
                'leaveType:id,name',
            ]);

            $this->notifyApprover($leaveRequest, $manager);
            $createdRequests[] = $leaveRequest;
        }

        // Create one final/master row for HOD
        $finalLeaveRequest = EmployeLeaveRequest::create([
            'from_employee_id' => $fromEmployee->id,
            'to_employee_id' => $hodEmployee?->id,
            'from_user_id' => Auth::id(),
            'to_user_id' => $hodUser?->id,
            'department_id' => $fromEmployee->department_id,
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'duration' => $duration,
            'reason' => $validated['reason'] ?? null,
            'medical_report' => $medicalReportPath,
            'action_type' => 2,
            'status' => 0,
        ]);

        $finalLeaveRequest->load([
            'fromEmployee:id,full_name',
            'toEmployee:id,full_name',
            'leaveType:id,name',
        ]);

        if ($hodEmployee) {
            $this->notifyApprover($finalLeaveRequest, $hodEmployee);
        }

        $createdRequests[] = $finalLeaveRequest;

        return $finalLeaveRequest;
    }

    private function resolveParentApprovers(Employee $fromEmployee): \Illuminate\Support\Collection
    {
        $currentRole = $fromEmployee->role;

        if (!$currentRole || !$currentRole->parent_role_id) {
            return collect();
        }

        $parentRole = Role::find($currentRole->parent_role_id);

        if (!$parentRole) {
            return collect();
        }

        $query = Employee::query()
            ->where('is_active', true)
            ->where('id', '!=', $fromEmployee->id)
            ->where('role_id', $parentRole->id);

        if (!empty($fromEmployee->organization_id)) {
            $query->where('organization_id', $fromEmployee->organization_id);
        }

        if (!empty($parentRole->department_id)) {
            $query->where('department_id', $fromEmployee->department_id);
        }

        return $query->orderBy('id')->get();
    }

    private function notifyApprover(EmployeLeaveRequest $leaveRequest, ?Employee $toEmployee): void
    {
        if (!$toEmployee) {
            return;
        }

        $approverUser = User::where('employee_id', $toEmployee->id)->first();

        if (!$approverUser) {
            return;
        }

        $approverUser->notify(new LeaveApprovalNotification($leaveRequest));
    }

    public function leaveTypesForEmployee(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        $employee = Employee::with('role')->findOrFail((int) $request->input('employee_id'));

        $organizationId = $employee->organization_id;
        $departmentId = $employee->department_id;

        $leaveTypes = LeaveType::query()
            ->where('is_active', true)
            ->when($organizationId, fn($q) => $q->where(function ($qq) use ($organizationId) {
                $qq->whereNull('organization_id')->orWhere('organization_id', $organizationId);
            }))
            ->when($departmentId, fn($q) => $q->where(function ($qq) use ($departmentId) {
                $qq->whereNull('department_id')->orWhere('department_id', $departmentId);
            }))
            ->orderBy('name')
            ->get(['id', 'name']);

        $quotaSummary = $this->getPersonalQuotaSummary($employee->id);

        return response()->json([
            'success' => true,
            'leaveTypes' => $leaveTypes,
            'quotaSummary' => $quotaSummary,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|integer|in:0,1,2,3,4,5',
        ]);

        $leaveRequest = EmployeLeaveRequest::findOrFail($id);
        $newStatus = (int) $request->input('status');
        $currentStatus = (int) $leaveRequest->status;

        $currentUser = Auth::user();
        $isSuperAdmin = \DB::table('user_roles')
            ->where('user_id', $currentUser->id)
            ->where('role_id', 1)
            ->exists();

        if (!$isSuperAdmin) {
            $isAssigned = $leaveRequest->to_employee_id === optional($currentUser->employee)->id;

            if (!$isAssigned) {
                $msg = 'You do not have permission to act on this request.';
                return $request->expectsJson()
                    ? response()->json(['success' => false, 'message' => $msg], 403)
                    : abort(403, $msg);
            }

            if ((int) $leaveRequest->action_type === 1) {
                if (!in_array($currentStatus, [0, 1, 2])) {
                    $msg = 'You can only act on pending or recommended requests.';
                    return $request->expectsJson()
                        ? response()->json(['success' => false, 'message' => $msg], 403)
                        : abort(403, $msg);
                }

                if (!in_array($newStatus, [1, 2])) {
                    $msg = 'You can only recommend or not recommend.';
                    return $request->expectsJson()
                        ? response()->json(['success' => false, 'message' => $msg], 403)
                        : abort(403, $msg);
                }
            } elseif ((int) $leaveRequest->action_type === 2) {
                if (!in_array($newStatus, [3, 4, 5])) {
                    $msg = 'You can only approve, reject or cancel.';
                    return $request->expectsJson()
                        ? response()->json(['success' => false, 'message' => $msg], 403)
                        : abort(403, $msg);
                }
            } else {
                $msg = 'This request has no valid action type assignment.';
                return $request->expectsJson()
                    ? response()->json(['success' => false, 'message' => $msg], 403)
                    : abort(403, $msg);
            }
        }

        $leaveRequest->status = $newStatus;
        $leaveRequest->save();

        $actorName = Auth::user()->name ?? 'System';

        $statusLabel = match ($newStatus) {
            1 => 'recommended',
            2 => 'not recommended',
            3 => 'approved',
            4 => 'rejected',
            5 => 'cancelled',
            default => 'pending',
        };

        $this->auditTrailService->log(
            action: $statusLabel,
            category: 'LeaveRequest',
            description: "Leave request #{$leaveRequest->id} for {$leaveRequest->fromEmployee->full_name} has been {$statusLabel} by {$actorName}.",
            auditable: $leaveRequest,
            context: ['old_status' => $currentStatus, 'new_status' => $newStatus]
        );

        // Notify original requester
        $requesterUser = User::where('id', $leaveRequest->from_user_id)->first();
        if ($requesterUser) {
            $requesterUser->notify(
                (new \App\Notifications\LeaveStatusUpdateNotification($leaveRequest, $actorName))
                    ->delay(now()->addSeconds(2))
            );
        }

        // Notify HOD only when final/master row is approved
        if ((int) $leaveRequest->action_type === 2 && (int) $newStatus === 3) {
            $fromEmployee = $leaveRequest->fromEmployee;

            if ($fromEmployee) {
                $hodUser = $this->resolveHeadOfDepartment($fromEmployee);

                if ($hodUser && $hodUser->id !== $requesterUser?->id) {
                    $hodUser->notify(
                        (new LeaveApprovedToHodNotification($leaveRequest, $actorName))
                            ->delay(now()->addSeconds(6))
                    );
                }
            }
        }

        // Sync final decision to all recommendation rows
        if ((int) $leaveRequest->action_type === 2 && in_array($newStatus, [3, 4, 5])) {
            EmployeLeaveRequest::where('from_employee_id', $leaveRequest->from_employee_id)
                ->where('leave_type_id', $leaveRequest->leave_type_id)
                ->where('start_date', $leaveRequest->start_date)
                ->where('end_date', $leaveRequest->end_date)
                ->where('action_type', 1)
                ->where('status', '!=', $newStatus)
                ->update(['status' => $newStatus]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Status updated successfully.');
    }

    private function resolveHeadOfDepartment(Employee $employee): ?User
    {
        if (!$employee->department_id || !$employee->organization_id) {
            return null;
        }

        $hodEmployee = Employee::query()
            ->where('is_active', true)
            ->where('id', '!=', $employee->id)
            ->where('organization_id', $employee->organization_id)
            ->where('department_id', $employee->department_id)
            ->whereHas('role', function ($roleQuery) use ($employee) {
                $roleQuery
                    ->where('organization_id', $employee->organization_id)
                    ->where('department_id', $employee->department_id)
                    ->whereNotNull('parent_role_id')
                    ->whereHas('parentRole', function ($parentQuery) use ($employee) {
                        $parentQuery
                            ->where('organization_id', $employee->organization_id)
                            ->whereNull('department_id')
                            ->whereNull('parent_role_id');
                    });
            })
            ->orderBy('id')
            ->first();

        if (!$hodEmployee) {
            return null;
        }

        return User::where('employee_id', $hodEmployee->id)->first();
    }
}