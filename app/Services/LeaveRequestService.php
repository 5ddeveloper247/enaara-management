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

        $leaveRequests = EmployeLeaveRequest::with([
            'fromEmployee.department:id,name',
            'fromEmployee:id,name,department_id,organization_id',
            'toEmployee:id,name,department_id,organization_id',
            'leaveType:id,name',
        ])
            ->latest('id')
            ->paginate(20);
        $statusMap = [
            0 => 'pending',
            1 => 'recommended',
            2 => 'not_recommended',
            3 => 'approved',
            4 => 'rejected',
            5 => 'cancelled',
        ];
        $mappedLeaveRequests = $leaveRequests->map(function ($request) use ($statusMap) {
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
                'approvalLevel' => 'Manager / HR',
                'pendingSince' => $request->created_at ? $request->created_at->diffForHumans() : '-',
                'balance' => 0
            ];
        })->values()->all();

        $pendingCount = $leaveRequests->whereIn('status', [0, 1, 2])->count();
        $approvedTodayCount = $leaveRequests->where('status', 3)->where('updated_at', '>=', now()->startOfDay())->count();
        $awayTodayCount = EmployeLeaveRequest::where('status', 3)
            ->where('start_date', '<=', now()->toDateString())
            ->where('end_date', '>=', now()->toDateString())
            ->count();
        $overdueCount = $leaveRequests->whereIn('status', [0, 1, 2])->where('created_at', '<', now()->subDays(2))->count();

        return view('admin.leave-requests.index', compact(
            'employees',
            'leaveTypes',
            'leaveRequests',
            'mappedLeaveRequests',
            'pendingCount',
            'approvedTodayCount',
            'awayTodayCount',
            'overdueCount'
        ));
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

        $toEmployee = $this->resolveParentApproverEmployee($fromEmployee);
        $approverUser = $toEmployee ? User::where('employee_id', $toEmployee->id)->first() : null;

        $leaveRequest = EmployeLeaveRequest::create([
            'from_employee_id' => $fromEmployee->id,
            'to_employee_id' => $toEmployee?->id,
            'from_user_id' => Auth::id(),
            'to_user_id' => $approverUser?->id,
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

        $this->notifyApprover($leaveRequest, $toEmployee);

        return $leaveRequest;
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
        $leaveRequest->status = $request->input('status');
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
