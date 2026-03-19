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

class LeaveRequestService
{
    public function index()
    {
        $employees = Employee::where('is_active', true)->orderBy('name')->get();
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();

        $currentUser = Auth::user();
        $currentEmployee = $currentUser?->employee;
        $currentRole = $currentEmployee?->role;

        $statusMap = [
            0 => 'pending',
            1 => 'recommended',
            2 => 'not_recommended',
            3 => 'approved',
            4 => 'rejected',
            5 => 'cancelled',
        ];

        $leaveRequests = EmployeLeaveRequest::with([
            'fromEmployee.department:id,name',
            'fromEmployee.role:id,parent_role_id,organization_id,department_id',
            'fromEmployee:id,name,department_id,organization_id,role_id',
            'toEmployee:id,name,department_id,organization_id,role_id',
            'leaveType:id,name',
        ])
            ->when($currentRole, function ($query) use ($currentRole) {
                $query->whereHas('fromEmployee.role', function ($roleQuery) use ($currentRole) {
                    $roleQuery->where(function ($q) use ($currentRole) {
                        // Logged in user is parent of requester
                        $q->where('parent_role_id', $currentRole->id)
                            ->where('organization_id', $currentRole->organization_id);

                        if (!empty($currentRole->department_id)) {
                            $q->where('department_id', $currentRole->department_id);
                        }
                    })->orWhere(function ($q) use ($currentRole) {
                        // Logged in user is child of requester
                        $q->where('id', $currentRole->parent_role_id)
                            ->where('organization_id', $currentRole->organization_id);

                        if (!empty($currentRole->department_id)) {
                            $q->where('department_id', $currentRole->department_id);
                        }
                    });
                });
            })
            ->latest('id')
            ->paginate(20);

        $isSuperAdmin = \DB::table('user_roles')->where('user_id', $currentUser->id)->where('role_id', 1)->exists();

        $mappedLeaveRequests = $leaveRequests->getCollection()->map(function ($request) use ($statusMap, $currentRole, $currentUser, $isSuperAdmin) {
            $isApprover = ($currentUser && $currentUser->id === $request->to_user_id);

            $requesterRole = optional($request->fromEmployee)->role;

            $isParent = false;
            $isChild = false;

            if ($currentRole && $requesterRole) {
                $sameOrganization = (int) $currentRole->organization_id === (int) $requesterRole->organization_id;

                $sameDepartment = true;
                if (!empty($currentRole->department_id) && !empty($requesterRole->department_id)) {
                    $sameDepartment = (int) $currentRole->department_id === (int) $requesterRole->department_id;
                }

                if ($sameOrganization && $sameDepartment) {
                    // I am parent of requester
                    if ((int) $requesterRole->parent_role_id === (int) $currentRole->id) {
                        $isParent = true;
                    }

                    // I am child of requester
                    if ((int) $currentRole->parent_role_id === (int) $requesterRole->id) {
                        $isChild = true;
                    }
                }
            }

            // Calculate balance for this specific leave type and employee
            $maxAllowed = (float) (optional($request->leaveType)->annual_quota ?? 0);
            $year = Carbon::parse($request->start_date)->year;
            
            $claimed = (float) EmployeLeaveRequest::where('from_employee_id', $request->from_employee_id)
                ->where('leave_type_id', $request->leave_type_id)
                ->whereYear('start_date', $year)
                ->whereIn('status', [0, 1, 3])
                ->sum('duration');
            
            $remaining = max(0, $maxAllowed - $claimed);

            return [
                'id' => $request->id,
                'employeeName' => optional($request->fromEmployee)->name ?? 'Unknown',
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
                'approvalLevel' => $isParent ? 'Parent Approval' : ($isChild ? 'Child Recommendation' : '-'),
                'pendingSince' => $request->created_at ? $request->created_at->diffForHumans() : '-',
                'balance' => $remaining . ' / ' . $maxAllowed,
                'isParent' => $isParent,
                'isChild' => $isChild,
                'isApprover' => $isApprover,
                'isSuperAdmin' => $isSuperAdmin,
                'canApprove' => $isParent || $isSuperAdmin,
                'canReject' => $isParent || $isSuperAdmin,
                'canCancel' => $isParent || $isSuperAdmin,
                'canRecommend' => $isChild && !$isParent && in_array($request->status, [0, 1, 2]),
                'canNotRecommend' => $isChild && !$isParent && in_array($request->status, [0, 1, 2]),
            ];
        })->values()->all();

        $pendingCount = EmployeLeaveRequest::whereIn('status', [0, 1, 2])->count();
        $approvedTodayCount = EmployeLeaveRequest::where('status', 3)
            ->where('updated_at', '>=', now()->startOfDay())
            ->count();
        $awayTodayCount = EmployeLeaveRequest::where('status', 3)
            ->where('start_date', '<=', now()->toDateString())
            ->where('end_date', '>=', now()->toDateString())
            ->count();
        $overdueCount = EmployeLeaveRequest::whereIn('status', [0, 1, 2])
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
        
        $organizationId = $employee?->role?->organization_id;
        $departmentId = $employee?->role?->department_id;

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
            $maxAllowed = (float) $type->annual_quota;
            
            $claimed = (float) EmployeLeaveRequest::where('from_employee_id', $employeeId)
                ->where('leave_type_id', $type->id)
                ->whereYear('start_date', $year)
                ->whereIn('status', [0, 1, 3])
                ->sum('duration');

            $summary[] = [
                'type' => $type->name,
                'total' => $maxAllowed,
                'used' => $claimed,
                'remaining' => max(0, $maxAllowed - $claimed),
                'percentage' => $maxAllowed > 0 ? min(100, round(($claimed / $maxAllowed) * 100)) : 0
            ];
        }
        return $summary;
    }

    public function store(array $validated): EmployeLeaveRequest
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

        // Date Conflict Check (No overlapping leaves)
        $hasConflict = EmployeLeaveRequest::where('from_employee_id', $fromEmployee->id)
            ->whereIn('status', [0, 1, 3]) // 0:pending, 1:recommended, 3:approved
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

        // Quota Check
        $year = $startDate->year;
        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);
        $maxAllowed = (float) ($leaveType->annual_quota ?? 0);

        // Sum up all days already claimed for this year (Pending, Recommended, or Approved)
        $alreadyClaimed = (float) EmployeLeaveRequest::where('from_employee_id', $fromEmployee->id)
            ->where('leave_type_id', $validated['leave_type_id'])
            ->whereYear('start_date', $year)
            ->whereIn('status', [0, 1, 3]) // 0:pending, 1:recommended, 3:approved
            ->sum('duration');

        if (($alreadyClaimed + $duration) > $maxAllowed) {
            $remaining = max(0, $maxAllowed - $alreadyClaimed);
            throw ValidationException::withMessages([
                'leave_type_id' => "Insufficient leave balance. You have {$remaining} day(s) remaining for this leave type in {$year}, but you are requesting {$duration} day(s)."
            ]);
        }

        $parentApprover = $this->resolveParentApproverEmployee($fromEmployee);
        $childApprovers = $this->resolveChildEmployees($fromEmployee);

        // Create the single master leave request
        $parentApproverUser = $parentApprover ? User::where('employee_id', $parentApprover->id)->first() : null;
        
        $leaveRequest = EmployeLeaveRequest::create([
            'from_employee_id' => $fromEmployee->id,
            'to_employee_id' => $parentApprover?->id,
            'from_user_id' => Auth::id(),
            'to_user_id' => $parentApproverUser?->id,
            'department_id' => $fromEmployee->department_id,
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'duration' => $duration,
            'reason' => $validated['reason'] ?? null,
            'status' => 0,
        ]);

        $leaveRequest->load([
            'fromEmployee:id,name',
            'toEmployee:id,name',
            'leaveType:id,name',
        ]);

        // Notify Parent
        if ($parentApprover) {
            $this->notifyApprover($leaveRequest, $parentApprover);
        }

        // Notify all Children
        foreach ($childApprovers as $child) {
            $this->notifyApprover($leaveRequest, $child);
        }

        return $leaveRequest;
    }

    private function resolveChildEmployees(Employee $fromEmployee): \Illuminate\Support\Collection
    {
        $currentRole = $fromEmployee->role;

        if (!$currentRole) {
            return collect();
        }

        // Find all roles that have this role as parent
        $childRoles = Role::where('parent_role_id', $currentRole->id)
            ->where('is_active', true)
            ->where('organization_id', $currentRole->organization_id)
            ->get();

        if ($childRoles->isEmpty()) {
            return collect();
        }

        return Employee::query()
            ->where('is_active', true)
            ->where('id', '!=', $fromEmployee->id)
            ->whereIn('role_id', $childRoles->pluck('id'))
            ->where('department_id', $fromEmployee->department_id)
            ->get();
    }

    private function resolveParentApproverEmployee(Employee $fromEmployee): ?Employee
    {
        $currentRole = $fromEmployee->role;

        if (!$currentRole || !$currentRole->parent_role_id) {
            return null;
        }

        $parentRole = Role::find($currentRole->parent_role_id);

        if (!$parentRole) {
            return null;
        }

        if ((int) $parentRole->organization_id !== (int) $currentRole->organization_id) {
            return null;
        }

        $query = Employee::query()
            ->where('is_active', true)
            ->where('id', '!=', $fromEmployee->id)
            ->where('role_id', $parentRole->id);

        if (!empty($parentRole->department_id)) {
            $query->where('department_id', $fromEmployee->department_id);
        }

        return $query->orderBy('id')->first();
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

        $organizationId = $employee->role?->organization_id;
        $departmentId = $employee->role?->department_id;

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
        return response()->json([
            'success' => true,
            'leaveTypes' => $leaveTypes,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|integer|in:0,1,2,3,4,5'
        ]);

        $leaveRequest = EmployeLeaveRequest::findOrFail($id);
        $newStatus = (int) $request->input('status');
        $currentStatus = (int) $leaveRequest->status;

        $currentUser = Auth::user();
        $isSuperAdmin = \DB::table('user_roles')->where('user_id', $currentUser->id)->where('role_id', 1)->exists();

        $currentEmployee = $currentUser->employee;
        $currentRole = optional($currentEmployee)->role;

        $requesterEmployee = $leaveRequest->fromEmployee;
        $requesterRole = optional($requesterEmployee)->role;

        // Role check logic
        $isParent = false;
        $isChild = false;

        if ($currentRole && $requesterRole) {
            $sameOrganization = (int) $currentRole->organization_id === (int) $requesterRole->organization_id;
            $sameDepartment = true;
            if (!empty($currentRole->department_id) && !empty($requesterRole->department_id)) {
                $sameDepartment = (int) $currentRole->department_id === (int) $requesterRole->department_id;
            }

            if ($sameOrganization && $sameDepartment) {
                if ((int) $requesterRole->parent_role_id === (int) $currentRole->id) {
                    $isParent = true;
                }
                if ((int) $currentRole->parent_role_id === (int) $requesterRole->id) {
                    $isChild = true;
                }
            }
        }

        // Permissions Validations
        if (!$isSuperAdmin) {
            if ($isChild && !$isParent) {
                if (!in_array($currentStatus, [0, 1, 2])) {
                    $msg = 'Children can only act on pending or recommended requests.';
                    return $request->expectsJson() ? response()->json(['success' => false, 'message' => $msg], 403) : abort(403, $msg);
                }
                if (!in_array($newStatus, [1, 2])) {
                    $msg = 'Children can only recommend or not recommend.';
                    return $request->expectsJson() ? response()->json(['success' => false, 'message' => $msg], 403) : abort(403, $msg);
                }
            } elseif ($isParent) {
                if (!in_array($newStatus, [3, 4, 5])) {
                    $msg = 'Parents can only approve, reject or cancel.';
                    return $request->expectsJson() ? response()->json(['success' => false, 'message' => $msg], 403) : abort(403, $msg);
                }
            } else {
                $msg = 'You do not have permission to act on this request.';
                return $request->expectsJson() ? response()->json(['success' => false, 'message' => $msg], 403) : abort(403, $msg);
            }
        }

        $leaveRequest->status = $newStatus;
        $leaveRequest->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Status updated successfully.');
    }
}
