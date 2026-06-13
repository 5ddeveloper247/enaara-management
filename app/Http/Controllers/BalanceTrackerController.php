<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BalanceTrackerService;
use App\Services\EmployeeViewerScopeService;
use App\Http\Requests\Admin\BalanceTracker\BalanceTrackerAdjustRequest;
use App\Models\Organization;
use App\Models\Department;
use Illuminate\Validation\ValidationException;

class BalanceTrackerController extends Controller
{
    protected BalanceTrackerService $balanceTrackerService;

    protected EmployeeViewerScopeService $viewerScope;

    public function __construct(
        BalanceTrackerService $balanceTrackerService,
        EmployeeViewerScopeService $viewerScope,
    ) {
        $this->balanceTrackerService = $balanceTrackerService;
        $this->viewerScope = $viewerScope;
    }

    public function index(Request $request)
    {
        if (!validatePermissions('admin/balance-tracker')) {
            abort(403, 'Unauthorized action.');
        }

        $organization = $request->query('organization');
        $department = $request->query('department');

        $data = $this->balanceTrackerService->getBalances($organization, $department);
        $balances = $data['balances'];
        $leaveTypes = $data['leaveTypes'];
        
        $organizations = Organization::where('is_active', true)->orderBy('name', 'asc')->get();
        $departments = Department::where('is_active', true)->orderBy('name', 'asc')->get();
        $organizations = $this->viewerScope->filterOrganizations($organizations);
        $departments = $this->viewerScope->filterDepartments($departments);
        $viewerEmployeeScope = $this->viewerScope->frontendScopePayload();

        return view('admin.balance-tracker.index', compact(
            'balances',
            'leaveTypes',
            'organizations',
            'departments',
            'viewerEmployeeScope',
        ));
    }

    public function adjustBalance(BalanceTrackerAdjustRequest $request)
    {
        if (!validatePermissions('admin/balance-tracker')) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        try {
            $this->balanceTrackerService->adjustBalance($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Leave balance successfully adjusted.'
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Unauthorized.',
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function export(Request $request)
    {
        if (!validatePermissions('admin/balance-tracker')) {
            abort(403, 'Unauthorized action.');
        }

        $organization = $request->query('organization');
        $department = $request->query('department');

        $data = $this->balanceTrackerService->getBalances($organization, $department);
        $balances = $data['balances'];
        $leaveTypes = $data['leaveTypes'];

        return response()->view('admin.balance-tracker.export', compact('balances', 'leaveTypes'))
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="Balance_Tracker_Export_' . now()->format('Y-m-d') . '.xls"');
    }
}
