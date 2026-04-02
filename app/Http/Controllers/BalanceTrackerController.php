<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BalanceTrackerService;
use App\Http\Requests\Admin\BalanceTracker\BalanceTrackerAdjustRequest;
use App\Models\Organization;
use App\Models\Department;

class BalanceTrackerController extends Controller
{
    protected $balanceTrackerService;

    public function __construct(BalanceTrackerService $balanceTrackerService)
    {
        $this->balanceTrackerService = $balanceTrackerService;
    }

    public function index(Request $request)
    {
        if (!validatePermissions('admin/balance-tracker')) {
            abort(403, 'Unauthorized action.');
        }

        $organization = $request->query('organization');
        $department = $request->query('department');

        $balances = $this->balanceTrackerService->getBalances($organization, $department);
        
        $organizations = Organization::where('is_active', true)->orderBy('name', 'asc')->get();
        $departments = Department::where('is_active', true)->orderBy('name', 'asc')->get();

        return view('admin.balance-tracker.index', compact(
            'balances',
            'organizations',
            'departments'
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
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function export(Request $request)
    {
        if (!validatePermissions('admin/balance-tracker')) {
            abort(403, 'Unauthorized action.');
        }

        $organization = $request->query('organization');
        $department = $request->query('department');

        $balances = $this->balanceTrackerService->getBalances($organization, $department);

        return response()->view('admin.balance-tracker.export', compact('balances'))
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="Balance_Tracker_Export_' . now()->format('Y-m-d') . '.xls"');
    }
}
