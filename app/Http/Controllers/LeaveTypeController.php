<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\LeaveType\LeaveTypeStoreRequest;
use App\Http\Requests\Admin\LeaveType\LeaveTypeUpdateRequest;
use App\Models\Organization;
use App\Services\LeaveTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class LeaveTypeController extends Controller
{
    public function __construct(
        private LeaveTypeService $leaveTypeService
    ) {}

    public function index(): View
    {
        $leaveTypes = $this->leaveTypeService->getList();
        $counts = $this->leaveTypeService->getCounts();
        $organizations = Organization::orderBy('name')->get(['id', 'name']);

        return view('admin.leave-type.index', [
            'leaveTypes' => $leaveTypes,
            'organizations' => $organizations,
            'total' => $counts['total'],
            'conditionalTotal' => $counts['conditional_total'],
            'unconditionalQuotaSum' => $counts['unconditional_quota_sum'],
        ]);
    }

    public function entitlementReference(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'organization_id' => ['required', 'integer', 'exists:organizations,id'],
            'sbu_ids' => ['nullable', 'array'],
            'sbu_ids.*' => ['integer', 'exists:sbus,id'],
            'exclude_id' => ['nullable', 'integer', 'exists:leave_types,id'],
        ]);

        try {
            $rows = $this->leaveTypeService->getEntitlementReference(
                (int) $validated['organization_id'],
                $validated['sbu_ids'] ?? [],
                isset($validated['exclude_id']) ? (int) $validated['exclude_id'] : null,
            );

            return response()->json([
                'success' => true,
                'rows' => $rows,
            ]);
        } catch (\Exception $e) {
            Log::error('Leave type entitlement reference failed', [
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load entitlement reference.',
                'rows' => [],
            ], 500);
        }
    }

    public function create(): View
    {
        // if (! validatePermissions('admin/leave-type/add')) {
        //     abort(403, 'Unauthorized action.');
        // }

        $organizations = Organization::orderBy('name')->get(['id', 'name']);
        $roleLevels = \App\Models\RoleLevel::excludingSystemAdmin()->where('is_active', true)->orderBy('level')->get(['id', 'name', 'level']);

        return view('admin.leave-type.create', [
            'organizations' => $organizations,
            'roleLevels' => $roleLevels,
        ]);
    }

    public function store(LeaveTypeStoreRequest $request)
    {
        // if (! validatePermissions('admin/leave-type/add')) {
        //     if ($request->expectsJson() || $request->ajax()) {
        //         return response()->json([
        //             'success' => false,
        //             'message' => 'Unauthorized action.',
        //         ], 403);
        //     }

        //     abort(403, 'Unauthorized action.');
        // }

        try {
            $this->leaveTypeService->store($request->validated());

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Leave type created successfully.',
                ]);
            }

            return redirect()
                ->route('admin.leave.type.index')
                ->with('success', 'Leave type created successfully.');
        } catch (\Exception $e) {
            Log::error('Leave type create failed', [
                'exception' => $e->getMessage(),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create leave type.',
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create leave type.');
        }
    }

    public function edit($id): View|\Illuminate\Http\JsonResponse
    {
        // if (! validatePermissions('admin/leave-type/edit')) {
        //     if (request()->expectsJson() || request()->ajax()) {
        //         return response()->json([
        //             'success' => false,
        //             'message' => 'Unauthorized action.',
        //         ], 403);
        //     }

        //     abort(403, 'Unauthorized action.');
        // }

        try {
            $leaveType = $this->leaveTypeService->findById((int) $id);

            if (! $leaveType) {
                if (request()->expectsJson() || request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Leave type not found.',
                    ], 404);
                }

                abort(404);
            }

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $this->leaveTypeService->formatForForm($leaveType),
                ]);
            }

            $organizations = Organization::orderBy('name')->get(['id', 'name']);
            $roleLevels = \App\Models\RoleLevel::excludingSystemAdmin()->where('is_active', true)->orderBy('level')->get(['id', 'name', 'level']);

            return view('admin.leave-type.create', [
                'organizations' => $organizations,
                'roleLevels' => $roleLevels,
                'leaveType' => $leaveType,
                'initialData' => $this->leaveTypeService->formatForForm($leaveType),
                'isEdit' => true,
            ]);
        } catch (\Exception $e) {
            Log::error('Leave type edit fetch failed', [
                'leave_type_id' => $id,
                'exception' => $e->getMessage(),
            ]);

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch leave type.',
                ], 500);
            }

            abort(500);
        }
    }

    public function update(LeaveTypeUpdateRequest $request, $id)
    {
        // if (! validatePermissions('admin/leave-type/edit')) {
        //     if ($request->expectsJson() || $request->ajax()) {
        //         return response()->json([
        //             'success' => false,
        //             'message' => 'Unauthorized action.',
        //         ], 403);
        //     }

        //     abort(403, 'Unauthorized action.');
        // }

        try {
            $this->leaveTypeService->update((int) $id, $request->validated());

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Leave type updated successfully.',
                ]);
            }

            return redirect()
                ->route('admin.leave.type.index')
                ->with('success', 'Leave type updated successfully.');
        } catch (\Exception $e) {
            Log::error('Leave type update failed', [
                'leave_type_id' => $id,
                'exception' => $e->getMessage(),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update leave type.',
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update leave type.');
        }
    }

    public function destroy($id)
    {
        // if (! validatePermissions('admin/leave-type/delete')) {
        //     if (request()->expectsJson() || request()->ajax()) {
        //         return response()->json([
        //             'success' => false,
        //             'message' => 'Unauthorized action.',
        //         ], 403);
        //     }

        //     abort(403, 'Unauthorized action.');
        // }

        try {
            $this->leaveTypeService->destroy((int) $id);

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Leave type deleted successfully.',
                ]);
            }

            return redirect()
                ->route('admin.leave.type.index')
                ->with('success', 'Leave type deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Leave type delete failed', [
                'leave_type_id' => $id,
                'exception' => $e->getMessage(),
            ]);

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete leave type.',
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Failed to delete leave type.');
        }
    }
}
