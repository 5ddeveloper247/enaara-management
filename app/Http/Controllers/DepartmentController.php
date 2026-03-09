<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function __construct(
        private DepartmentService $departmentService
    ) {}

    public function index(): View|\Illuminate\Http\JsonResponse
    {
        $departments = $this->departmentService->getList();
        $organizations = $this->departmentService->getOrganizationsForFilter();
        $sbus = $this->departmentService->getSbusForFilter();
        $counts = $this->departmentService->getCounts();
        
        if (request()->expectsJson() || request()->wantsJson()) {
            $organizationId = request()->get('organization_id');
            if ($organizationId) {
                $filteredDepartments = $departments->filter(function($dept) use ($organizationId) {
                    return $dept->organization_id == $organizationId;
                })->map(function($dept) {
                    return [
                        'id' => $dept->id,
                        'name' => $dept->name,
                        'organization_id' => $dept->organization_id,
                    ];
                })->values();
                return response()->json(['departments' => $filteredDepartments]);
            }
            $departmentsArray = $departments->map(function($dept) {
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
            'activePercentage' => $counts['active_percentage'],
        ]);
    }

    public function create(): View|\Illuminate\Http\JsonResponse
    {
        $organizations = $this->departmentService->getOrganizationsForFilter();
        $sbus = $this->departmentService->getSbusForFilter();
        $departments = Department::with('organization')->orderBy('name')->get(['id', 'name', 'organization_id']);
        
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

    public function store(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'organization_id' => 'required|exists:organizations,id',
                'sbu_id' => 'required|exists:sbus,id',
                'name' => 'required|string|max:255',
                'code' => [
                    'nullable',
                    'string',
                    'max:64',
                    Rule::unique('departments')->where('organization_id', $request->input('organization_id')),
                ],
                'parent_department_id' => 'nullable|exists:departments,id',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
            ]);
            $validated['is_active'] = $request->boolean('is_active');
            $department = $this->departmentService->create($validated);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Department created successfully.',
                    'department' => $department,
                ]);
            }
            
            return redirect()->route('admin.department.index')->with('success', 'Department created successfully.');
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

    public function edit(int $id): View|RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $department = $this->departmentService->findById($id);
        if (!$department) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Department not found'], 404);
            }
            abort(404);
        }
        
        if (request()->expectsJson()) {
            $organizations = $this->departmentService->getOrganizationsForFilter();
            $sbus = $this->departmentService->getSbusForFilter();
            $parentDepartments = Department::with('organization')
                ->where('id', '!=', $id)
                ->orderBy('name')
                ->get(['id', 'name', 'organization_id']);
            
            return response()->json([
                'department' => $department,
                'organizations' => $organizations,
                'sbus' => $sbus,
                'parentDepartments' => $parentDepartments,
            ]);
        }
        
        $organizations = $this->departmentService->getOrganizationsForFilter();
        $sbus = $this->departmentService->getSbusForFilter();
        $parentDepartments = Department::with('organization')->orderBy('name')->get(['id', 'name', 'organization_id']);

        return view('admin.departments.edit', [
            'department' => $department,
            'organizations' => $organizations,
            'sbus' => $sbus,
            'parentDepartments' => $parentDepartments,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $department = $this->departmentService->findById($id);
        if (!$department) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Department not found'], 404);
            }
            abort(404);
        }
        
        try {
            $validated = $request->validate([
                'organization_id' => 'required|exists:organizations,id',
                'sbu_id' => 'required|exists:sbus,id',
                'name' => 'required|string|max:255',
                'code' => [
                    'nullable',
                    'string',
                    'max:64',
                    Rule::unique('departments')->where('organization_id', $request->input('organization_id'))->ignore($department->id),
                ],
                'parent_department_id' => 'nullable|exists:departments,id',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
            ]);
            $validated['is_active'] = $request->boolean('is_active');
            if (isset($validated['parent_department_id']) && $validated['parent_department_id'] == $department->id) {
                $validated['parent_department_id'] = null;
            }
            $this->departmentService->update($department, $validated);
            
            if ($request->expectsJson()) {
                $updatedDepartment = $this->departmentService->findById($id);
                return response()->json([
                    'success' => true,
                    'message' => 'Department updated successfully.',
                    'department' => $updatedDepartment,
                ]);
            }
            
            return redirect()->route('admin.department.index')->with('success', 'Department updated successfully.');
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
}
