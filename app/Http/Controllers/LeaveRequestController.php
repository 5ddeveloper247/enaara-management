<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Admin\LeaveRequestStore;
use App\Services\LeaveRequestService;
use Illuminate\Http\RedirectResponse;
use App\Models\Employee;
use App\Models\LeaveType;
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

        // Fallback for UI charts if specific types weren't found in DB
        $mappedQuota = [
            'annual' => ['used' => 0, 'total' => 0, 'remaining' => 0],
            'sick' => ['used' => 0, 'total' => 0, 'remaining' => 0],
            'casual' => ['used' => 0, 'total' => 0, 'remaining' => 0],
            'compOff' => ['used' => 0, 'total' => 0, 'remaining' => 0],
        ];

        foreach ($personalQuota as $q) {
            $name = strtolower($q['type']);
            if (str_contains($name, 'annual')) $mappedQuota['annual'] = $q;
            elseif (str_contains($name, 'sick')) $mappedQuota['sick'] = $q;
            elseif (str_contains($name, 'casual')) $mappedQuota['casual'] = $q;
            elseif (str_contains($name, 'comp')) $mappedQuota['compOff'] = $q;
        }

        return view('admin.my-leaves.index', [
            'personalQuota' => $mappedQuota,
            'personalHistory' => $personalHistory,
            'employees' => Employee::where('is_active', true)->orderBy('name')->get(),
            'leaveTypes' => LeaveType::where('is_active', true)->orderBy('name')->get(),
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
        $leaveRequest = $this->leaveRequestService->store($request->validated());

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
