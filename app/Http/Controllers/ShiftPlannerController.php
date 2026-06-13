<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ShiftPlannerService;
use App\Services\ShiftRosterService;
use App\Services\EmployeeViewerScopeService;
use App\Http\Requests\Admin\ShiftPlanner\ShiftPlannerRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\OutsourcedEmployee;
use App\Models\ShiftRosterEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ShiftPlannerController extends Controller
{
    protected $shiftPlannerService;

    public function __construct(
        ShiftPlannerService $shiftPlannerService,
        private readonly ShiftRosterService $shiftRosterService,
        private readonly EmployeeViewerScopeService $viewerScope,
    ) {
        $this->shiftPlannerService = $shiftPlannerService;
    }

    public function index()
    {
        if (!validatePermissions('admin/shift-planner')) {
            abort(403, 'Unauthorized action.');
        }

        $employees = Employee::with('department')
            ->where('is_active', 1)
            ->shiftBasedWorkArrangement()
            ->orderBy('full_name')
            ->get();

        $outsourcedEmployees = OutsourcedEmployee::with('contractorCompany')
            ->whereNull('deleted_at')
            ->orderBy('full_name')
            ->get();

        [$employees, $outsourcedEmployees] = $this->shiftRosterService->scopeAssigneesForViewer(
            $employees,
            $outsourcedEmployees,
            Auth::id()
        );

        $departmentIds = $this->shiftRosterService->viewerRosterDepartmentIds(Auth::id());
        if ($departmentIds === null) {
            $departments = Department::query()->orderBy('name')->get();
        } elseif ($departmentIds === []) {
            $departments = collect();
        } else {
            $departments = Department::query()
                ->whereIn('id', $departmentIds)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        $shifts = $this->shiftPlannerService->getAllForManagement();
        $organizations = $this->shiftPlannerService->getOrganizationHierarchy();
        $viewerEmployeeScope = $this->viewerScope->frontendScopePayload();

        $rosters = ShiftRosterEntry::with(['employee.department', 'outsourcedEmployee.contractorCompany', 'shift'])
            ->where(function ($query) {
                $query->whereHas('employee', fn ($q) => $q->where('engagement_mode', 'shifts'))
                    ->orWhereHas('outsourcedEmployee');
            })
            ->orderBy('roster_date', 'asc')
            ->get();

        return view('admin.shift-planner.index', compact(
            'employees',
            'outsourcedEmployees',
            'shifts',
            'rosters',
            'departments',
            'organizations',
            'viewerEmployeeScope',
        ));
    }

    public function show($id)
    {
        if (!validatePermissions('admin/shift-planner')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        try {
            $shift = $this->shiftPlannerService->findAccessible((int) $id);

            if ($shift === null) {
                return response()->json(['success' => false, 'message' => 'Shift not found.'], 404);
            }

            return response()->json([
                'success' => true,
                'shift' => $shift,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Shift not found.'], 404);
        }
    }

    public function store(ShiftPlannerRequest $request)
    {
        if (!validatePermissions('admin/shift-planner')) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        try {
            $this->shiftPlannerService->store($request->validated());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Shift created successfully.',
                ]);
            }

            return redirect()
                ->route('admin.shift-planner.index')
                ->with('success', 'Shift created successfully.');
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors(),
                ], 422);
            }

            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create shift: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create shift: ' . $e->getMessage());
        }
    }

    public function update(ShiftPlannerRequest $request, $id)
    {
        if (!validatePermissions('admin/shift-planner')) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        try {
            $this->shiftPlannerService->update($request->validated(), $id);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Shift updated successfully.',
                ]);
            }

            return redirect()
                ->route('admin.shift-planner.index')
                ->with('success', 'Shift updated successfully.');
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors(),
                ], 422);
            }

            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update shift: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update shift: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        if (!validatePermissions('admin/shift-planner')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        try {
            $this->shiftPlannerService->destroy($id);

            return response()->json([
                'success' => true,
                'message' => 'Shift deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete shift: ' . $e->getMessage(),
            ], 500);
        }
    }
}
