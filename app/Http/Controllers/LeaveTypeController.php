<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use App\Services\LeaveTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Rule;
use Illuminate\View\View;

class LeaveTypeController extends Controller
{
    public function __construct(
        private LeaveTypeService $leaveTypeService
    ) {}

    public function index(): View
    {
        $leaveTypes = $this->leaveTypeService->getList();
        $organizations = $this->leaveTypeService->getOrganizationsForFilter();
        $counts = $this->leaveTypeService->getCounts();

        return view('admin.leave-type.index', [
            'leaveTypes' => $leaveTypes,
            'organizations' => $organizations,
            'total' => $counts['total'],
            'active' => $counts['active'],
            'inactive' => $counts['inactive'],
        ]);
    }

    public function create(): View|\Illuminate\Http\JsonResponse
    {
        $organizations = $this->leaveTypeService->getOrganizationsForFilter();
        
        if (request()->expectsJson()) {
            return response()->json([
                'organizations' => $organizations,
            ]);
        }

        return view('admin.leave-type.create', [
            'organizations' => $organizations,
        ]);
    }

    public function store(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'organization_id' => 'required|exists:organizations,id',
                'sbu_id' => 'required|exists:sbus,id',
                'department_ids' => 'nullable|array',
                'department_ids.*' => 'exists:departments,id',
                'name' => 'required|string|max:255',
                'code' => 'nullable|string|max:64',
                'annual_quota' => 'required|numeric|min:0|max:999.99',
                'is_active' => 'boolean',
            ]);

            $isActive = $request->boolean('is_active');
            $deptIds = $request->input('department_ids', []);
            $orgId = $validated['organization_id'];

            try {
                DB::beginTransaction();
                
                $lt = $this->leaveTypeService->create([
                    'organization_id' => $orgId,
                    'sbu_id' => $validated['sbu_id'],
                    'name' => $validated['name'],
                    'code' => $validated['code'],
                    'annual_quota' => $validated['annual_quota'],
                    'is_active' => $isActive,
                ]);

                if (!empty($deptIds)) {
                    $lt->departments()->sync($deptIds);
                }

                DB::commit();

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Leave type created successfully.',
                    ]);
                }

                return redirect()->route('admin.leave.type.index')
                    ->with('success', 'Leave type created successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to create leave type: ' . $e->getMessage()
                    ], 500);
                }
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }
    }

    public function edit(int $id): View|\Illuminate\Http\JsonResponse
    {
        $leaveType = $this->leaveTypeService->findById($id);

        if (!$leaveType instanceof LeaveType) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Leave type not found'], 404);
            }
            abort(404);
        }
        
        if (request()->expectsJson()) {
            $organizations = $this->leaveTypeService->getOrganizationsForFilter();
            
            return response()->json([
                'leaveType' => $leaveType,
                'department_ids' => $leaveType->departments->pluck('id')->toArray(),
                'organizations' => $organizations,
            ]);
        }

        $organizations = $this->leaveTypeService->getOrganizationsForFilter();

        return view('admin.leave-type.edit', [
            'leaveType' => $leaveType,
            'organizations' => $organizations,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $leaveType = $this->leaveTypeService->findById($id);

        if (!$leaveType instanceof LeaveType) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Leave type not found'], 404);
            }
            abort(404);
        }
        
        try {
            $validated = $request->validate([
                'organization_id' => 'required|exists:organizations,id',
                'sbu_id' => 'required|exists:sbus,id',
                'department_ids' => 'nullable|array',
                'department_ids.*' => 'exists:departments,id',
                'name' => 'required|string|max:255',
                'code' => 'nullable|string|max:64',
                'annual_quota' => 'required|numeric|min:0|max:999.99',
                'is_active' => 'boolean',
            ]);

            $isActive = $request->boolean('is_active');
            $deptIds = $request->input('department_ids', []);
            $orgId = $validated['organization_id'];

            try {
                DB::beginTransaction();

                $this->leaveTypeService->update($leaveType, [
                    'organization_id' => $orgId,
                    'sbu_id' => $validated['sbu_id'],
                    'name' => $validated['name'],
                    'code' => $validated['code'],
                    'annual_quota' => $validated['annual_quota'],
                    'is_active' => $isActive,
                ]);

                // Sync departments (replaces old ones with new selection)
                $leaveType->departments()->sync($deptIds);

                DB::commit();

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Leave type updated successfully.',
                    ]);
                }

                return redirect()->route('admin.leave.type.index')
                    ->with('success', 'Leave type updated successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to update leave type: ' . $e->getMessage()
                    ], 500);
                }
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }
    }

    public function destroy(int $id): \Illuminate\Http\JsonResponse|RedirectResponse
    {
        try {
            $deleted = $this->leaveTypeService->destroy($id);

            if (!$deleted) {
                if (request()->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Leave type not found or could not be deleted'], 404);
                }
                return redirect()->route('admin.leave.type.index')
                    ->with('error', 'Leave type not found or could not be deleted.');
            }

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Leave type deleted successfully.',
                ]);
            }

            return redirect()->route('admin.leave.type.index')
                ->with('success', 'Leave type deleted successfully.');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the leave type.',
                    'error' => $e->getMessage()
                ], 500);
            }
            return redirect()->route('admin.leave.type.index')
                ->with('error', 'An error occurred while deleting the leave type.');
        }
    }
}

