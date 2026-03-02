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

    public function index(): View
    {
        $departments = $this->departmentService->getList();
        $organizations = $this->departmentService->getOrganizationsForFilter();
        $sbus = $this->departmentService->getSbusForFilter();
        $counts = $this->departmentService->getCounts();

        return view('admin.departments.index', [
            'departments' => $departments,
            'organizations' => $organizations,
            'sbus' => $sbus,
            'totalDepartments' => $counts['total'],
            'activeDepartments' => $counts['active'],
            'activePercentage' => $counts['active_percentage'],
        ]);
    }

    public function create(): View
    {
        $organizations = $this->departmentService->getOrganizationsForFilter();
        $sbus = $this->departmentService->getSbusForFilter();
        $departments = Department::with('organization')->orderBy('name')->get(['id', 'name', 'organization_id']);

        return view('admin.departments.create', [
            'organizations' => $organizations,
            'sbus' => $sbus,
            'parentDepartments' => $departments,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
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
        $this->departmentService->create($validated);
        return redirect()->route('admin.department.index')->with('success', 'Department created successfully.');
    }

    public function edit(int $id): View|RedirectResponse
    {
        $department = $this->departmentService->findById($id);
        if (!$department) {
            abort(404);
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

    public function update(Request $request, int $id): RedirectResponse
    {
        $department = $this->departmentService->findById($id);
        if (!$department) {
            abort(404);
        }
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
        return redirect()->route('admin.department.index')->with('success', 'Department updated successfully.');
    }
}
