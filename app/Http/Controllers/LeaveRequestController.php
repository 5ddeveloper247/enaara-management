<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Admin\LeaveRequestStore;
use App\Services\LeaveRequestService;
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

    public function myLeaves()
    {
        $currentUser = Auth::user();
        if (!$currentUser || !$currentUser->employee) {
            return redirect()->route('admin.dashboard.index')->with('error', 'Employee profile not found.');
        }

        $employeeId = $currentUser->employee->id;
        $personalQuota = $this->leaveRequestService->getPersonalQuotaSummary($employeeId);
        $personalHistory = $this->leaveRequestService->getPersonalLeaveHistory($employeeId);

        return view('admin.my-leaves.index', [
            'personalQuota' => $personalQuota,
            'personalHistory' => $personalHistory,
            'employees' => Employee::where('is_active', true)->orderBy('full_name')->get(),
            'leaveTypes' => $this->leaveRequestService->getMyLeavesLeaveTypes(),
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
       return $this->leaveRequestService->leaveTypesForEmployee($request);
    }

    public function updateStatus(Request $request, $id)
    {
        return $this->leaveRequestService->updateStatus($request, $id);
    }
}
