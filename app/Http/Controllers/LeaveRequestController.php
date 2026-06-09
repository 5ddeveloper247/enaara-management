<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Admin\LeaveRequestStore;
use App\Services\LeaveRequestService;
use App\Services\leaverequestPrivatefunctions\LeaveRequestWorkflowPreviewService;
use Illuminate\Http\RedirectResponse;
use App\Models\Employee;
use Auth;
class LeaveRequestController extends Controller
{
    private LeaveRequestService $leaveRequestService;

    public function __construct(LeaveRequestService $leaveRequestService)
    {
        $this->leaveRequestService = $leaveRequestService;
    }

    public function index(){
        return $this->leaveRequestService->index();
    }

    public function myLeaves(LeaveRequestWorkflowPreviewService $workflowPreviewService)
    {
        $currentUser = Auth::user();
        if (!$currentUser || !$currentUser->employee) {
            return redirect()->route('admin.dashboard.index')->with('error', 'Employee profile not found.');
        }

        $employeeId = $currentUser->employee->id;
        $personalQuota = $this->leaveRequestService->getPersonalQuotaSummary($employeeId);
        $personalHistory = $this->leaveRequestService->getPersonalLeaveHistory($employeeId);

        return view('admin.my-leaves.index', [
            'personalQuota' => $this->leaveRequestService->filterPersonalQuotaForLeaveForm($personalQuota, $employeeId),
            'personalHistory' => $personalHistory,
            'employees' => Employee::where('is_active', true)->orderBy('full_name')->get(),
            'leaveTypes' => $this->leaveRequestService->getMyLeavesLeaveTypes(),
            'approvalWorkflowPreview' => $workflowPreviewService->previewForEmployee($currentUser->employee),
            'employeeOutstationAddresses' => $this->leaveRequestService->getEmployeeOutstationAddresses($currentUser->employee),
        ]);
    }

    public function create()
    {
        return $this->leaveRequestService->index();
    }

    public function store(LeaveRequestStore $request)
    {
        if(!validatePermissions('admin/leave-request/add')){
           abort(403, 'Unauthorized action.');
        }
        $leaveRequest = $this->leaveRequestService->store($request->validated(), $request);
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Leave request submitted successfully.',
                'leaveRequestId' => $leaveRequest->id,
            ]);
        }

        return redirect()
            ->route('admin.leave.request.index')
            ->with('success', 'Leave request submitted successfully.');
    }

    public function leaveTypesForEmployee(Request $request)
    {
        if (! validatePermissions('admin/leave-request/add')) {
            abort(403, 'Unauthorized action.');
        }

       return $this->leaveRequestService->leaveTypesForEmployee($request);
    }

    public function approvalWorkflowPreview(
        Request $request,
        LeaveRequestWorkflowPreviewService $workflowPreviewService
    ) {
        $currentUser = Auth::user();
        if (! $currentUser) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
        ]);

        $employeeId = (int) $validated['employee_id'];
        $canView = validatePermissions('admin/leave-request/add')
            || ($currentUser->employee_id && (int) $currentUser->employee_id === $employeeId);

        if (! $canView) {
            abort(403, 'Unauthorized action.');
        }

        $employee = Employee::query()->findOrFail($employeeId);

        return response()->json([
            'success' => true,
            ...$workflowPreviewService->previewForEmployee($employee),
        ]);
    }

    public function employeeAddresses(Request $request)
    {
        $currentUser = Auth::user();
        if (! $currentUser) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
        ]);

        $employeeId = (int) $validated['employee_id'];
        $canView = validatePermissions('admin/leave-request/add')
            || ($currentUser->employee_id && (int) $currentUser->employee_id === $employeeId);

        if (! $canView) {
            abort(403, 'Unauthorized action.');
        }

        $employee = Employee::query()->findOrFail($employeeId);

        return response()->json([
            'success' => true,
            ...$this->leaveRequestService->getEmployeeOutstationAddresses($employee),
        ]);
    }

    public function calculateDuration(Request $request)
    {
        if (! validatePermissions('admin/leave-request/add') && ! Auth::user()->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_half_day' => 'sometimes|boolean',
            'is_outstation_leave' => 'sometimes|boolean',
            'outstation_destination' => 'nullable|in:present,permanent',
        ]);

        $employee = Employee::findOrFail((int) $request->input('employee_id'));
        $startDate = \Carbon\Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = \Carbon\Carbon::parse($request->input('end_date'))->startOfDay();
        $isHalfDay = $request->boolean('is_half_day');
        $isOutstation = $request->boolean('is_outstation_leave');
        $destination = $isOutstation ? $request->input('outstation_destination') : null;

        $summary = $this->leaveRequestService->calculateLeaveDurationSummary(
            $employee,
            $startDate,
            $endDate,
            $isHalfDay,
            $isOutstation,
            $destination
        );

        return response()->json([
            'success' => true,
            ...$summary,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        return $this->leaveRequestService->updateStatus($request, $id);
    }
}
