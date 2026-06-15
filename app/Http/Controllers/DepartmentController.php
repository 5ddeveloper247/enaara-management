<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\Department\DepartmentStoreRequest;
use App\Http\Requests\Admin\Department\DepartmentUpdateRequest;
use App\Services\DepartmentService;
use App\Services\EmployeeViewerScopeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function __construct(
        private DepartmentService $departmentService,
        private EmployeeViewerScopeService $viewerScope,
    ) {}

    public function index(): View|\Illuminate\Http\JsonResponse
    {
        if (!validatePermissions('admin/department')) {
            if (request()->expectsJson() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        $departments = $this->departmentService->getList();
        $organizations = $this->departmentService->getOrganizationsForFilter();
        $sbus = $this->departmentService->getSbusForFilter();
        $counts = $this->departmentService->getCounts();

        if (request()->expectsJson() || request()->wantsJson()) {
            $organizationId = request()->get('organization_id');
            $sbuId = request()->get('sbu_id');

            if ($sbuId) {
                $filteredDepartments = $departments->filter(function ($dept) use ($sbuId) {
                    return $dept->sbu_id == $sbuId;
                })->map(function ($dept) {
                    return [
                        'id' => $dept->id,
                        'name' => $dept->name,
                        'sbu_id' => $dept->sbu_id,
                    ];
                })->values();

                return response()->json(['departments' => $filteredDepartments]);
            }

            if ($organizationId) {
                $filteredDepartments = $departments->filter(function ($dept) use ($organizationId) {
                    return $dept->organization_id == $organizationId;
                })->map(function ($dept) {
                    return [
                        'id' => $dept->id,
                        'name' => $dept->name,
                        'organization_id' => $dept->organization_id,
                    ];
                })->values();

                return response()->json(['departments' => $filteredDepartments]);
            }

            $departmentsArray = $departments->map(function ($dept) {
                return [
                    'id' => $dept->id,
                    'name' => $dept->name,
                    'organization_id' => $dept->organization_id,
                ];
            })->values();

            return response()->json(['departments' => $departmentsArray]);
        }

        return view('admin.departments.index', [
            'departments' => $departments,
            'organizations' => $organizations,
            'sbus' => $sbus,
            'totalDepartments' => $counts['total'],
            'activeDepartments' => $counts['active'],
            'inactiveDepartments' => $counts['inactive'],
            'activePercentage' => $counts['active_percentage'],
            'viewerEmployeeScope' => $this->viewerScope->frontendScopePayload(),
        ]);
    }

    public function create(): View|\Illuminate\Http\JsonResponse
    {
        $organizations = $this->departmentService->getOrganizationsForFilter();
        $sbus = $this->departmentService->getSbusForFilter();
        $departments = $this->departmentService->getParentDepartmentsForForm();

        if (request()->expectsJson()) {
            return response()->json([
                'organizations' => $organizations,
                'sbus' => $sbus,
                'parentDepartments' => $departments,
            ]);
        }

        return view('admin.departments.create', [
            'organizations' => $organizations,
            'sbus' => $sbus,
            'parentDepartments' => $departments,
        ]);
    }

    public function store(DepartmentStoreRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if (!validatePermissions('/admin/department/add')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        try {
            $validated = $request->validated();

            $department = $this->departmentService->create($validated);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Department created successfully.',
                    'department' => $department,
                ]);
            }

            return redirect()->route('admin.department.index')->with('success', 'Department created successfully.');
        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('Department create failed', [
                'exception' => $e->getMessage(),
            ]);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create department.',
                ], 500);
            }

            return redirect()->back()->withInput()->with('error', 'Failed to create department.');
        }
    }

    public function edit(int $id): View|RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if (!validatePermissions('admin/department/edit/{id}')) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        $department = $this->departmentService->findById($id);
        if (! $department) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Department not found'], 404);
            }
            abort(404);
        }

        if (request()->expectsJson()) {
            return response()->json([
                'department' => $department,
                'organizations' => $this->departmentService->getOrganizationsForFilter(),
                'sbus' => $this->departmentService->getSbusForFilter(),
                'parentDepartments' => $this->departmentService->getParentDepartmentsForForm($id),
            ]);
        }

        return view('admin.departments.edit', [
            'department' => $department,
            'organizations' => $this->departmentService->getOrganizationsForFilter(),
            'sbus' => $this->departmentService->getSbusForFilter(),
            'parentDepartments' => $this->departmentService->getParentDepartmentsForForm($id),
        ]);
    }

    public function update(DepartmentUpdateRequest $request, int $id): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if (!validatePermissions('admin/department/edit/{id}')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        $department = $this->departmentService->findById($id);
        if (! $department) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'Department not found'], 404);
            }
            abort(404);
        }

        try {
            $validated = $request->validated();

            $this->departmentService->update($department, $validated);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Department updated successfully.',
                    'department' => $this->departmentService->findById($id),
                ]);
            }

            return redirect()->route('admin.department.index')->with('success', 'Department updated successfully.');
        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('Department update failed', [
                'department_id' => $id,
                'exception' => $e->getMessage(),
            ]);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update department.',
                ], 500);
            }

            return redirect()->back()->withInput()->with('error', 'Failed to update department.');
        }
    }

    public function destroy(int $id): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if (!validatePermissions('/admin/department/delete')) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        $department = $this->departmentService->findById($id);

        if (! $department) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Department not found.',
                ], 404);
            }

            return redirect()->back()->with('error', 'Department not found.');
        }

        try {
            $this->departmentService->destroy($department);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Department deleted successfully.',
                ]);
            }

            return redirect()->route('admin.department.index')->with('success', 'Department deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Department delete failed', [
                'department_id' => $id,
                'exception' => $e->getMessage(),
            ]);
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete department.',
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to delete department.');
        }
    }
}
