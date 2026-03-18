<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Organization;
use App\Models\Department;

class RoleController extends Controller
{
    public function __construct(
        private RoleService $roleService
    ) {}

    public function index(): View
    {
        $roles = $this->roleService->getList();
        $counts = $this->roleService->getCounts();

        return view('admin.role.index', [
            'roles' => $roles,
            'total' => $counts['total'],
            'active' => $counts['active'],
            'inactive' => $counts['inactive'],
        ]);
    }

    public function create(): View
    {
        $moduleCategories = $this->roleService->getModuleCategoriesWithModules();
        $organizations = Organization::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $parentRoles = Role::where('is_active', true)->orderBy('name')->get();
        return view('admin.role.create', [
            'moduleCategories' => $moduleCategories,
            'organizations' => $organizations,
            'departments' => $departments,
            'parentRoles' => $parentRoles,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:roles,slug',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'is_primary' => 'boolean',
            'organization_id' => 'required|exists:organizations,id',
            'department_id' => 'nullable|exists:departments,id',
            'parent_role_id' => 'nullable|exists:roles,id',
            'module_ids' => 'nullable|array',
            'module_ids.*' => 'integer|exists:modules,id',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_primary'] = $request->boolean('is_primary');
        $validated['module_ids'] = $request->input('module_ids', []);

        $this->roleService->create($validated);

        return redirect()->route('admin.role.index')
            ->with('success', 'Role created successfully.');
    }

    public function show(int $id): View|RedirectResponse
    {
        $role = $this->roleService->findById($id);

        if (!$role instanceof Role) {
            abort(404);
        }

        return view('admin.role.show', [
            'role' => $role,
        ]);
    }

    public function edit(int $id): View|RedirectResponse
    {
        $role = $this->roleService->findById($id);

        if (!$role instanceof Role) {
            abort(404);
        }

        $moduleCategories = $this->roleService->getModuleCategoriesWithModules();
        $organizations = Organization::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $parentRoles = Role::where('is_active', true)
            ->where('id', '!=', $role->id)
            ->orderBy('name')
            ->get();
        return view('admin.role.edit', [
            'role' => $role,
            'moduleCategories' => $moduleCategories,
            'organizations' => $organizations,
            'departments' => $departments,
            'parentRoles' => $parentRoles,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $role = $this->roleService->findById($id);

        if (!$role instanceof Role) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:roles,slug,' . $role->id,
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'is_primary' => 'boolean',
            'organization_id' => 'required|exists:organizations,id',
            'department_id' => 'nullable|exists:departments,id',
            'parent_role_id' => 'nullable|exists:roles,id',
            'module_ids' => 'nullable|array',
            'module_ids.*' => 'integer|exists:modules,id',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_primary'] = $request->boolean('is_primary');
        $validated['module_ids'] = $request->input('module_ids', []);
        if (!empty($validated['parent_role_id']) && (int) $validated['parent_role_id'] === (int) $role->id) {
            return back()
                ->withErrors(['parent_role_id' => 'A role cannot be its own parent.'])
                ->withInput();
        }
        $this->roleService->update($role, $validated);

        return redirect()->route('admin.role.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->roleService->delete($id);

        if (!$deleted) {
            return response()->json(['success' => false, 'message' => 'Role not found.'], 404);
        }

        return response()->json(['success' => true]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate(['is_active' => 'required|boolean']);

        $role = $this->roleService->updateStatus($id, (bool) $request->input('is_active'));

        if (!$role) {
            return response()->json(['success' => false, 'message' => 'Role not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'is_active' => $role->is_active,
        ]);
    }

    public function searchRole(Request $request): JsonResponse
    {
        $term = $request->query('term', '');
        $items = $this->roleService->searchRole($term);

        return response()->json([
            'results' => $items->map(fn($r) => ['id' => $r->id, 'text' => $r->name]),
        ]);
    }

    public function getDepartmentsByOrganization(Request $request): JsonResponse
    {
        $request->validate([
            'organization_id' => 'nullable|exists:organizations,id',
        ]);

        $departments = Department::query()
            ->where('is_active', true)
            ->when($request->organization_id, function ($q) use ($request) {
                $q->where('organization_id', $request->organization_id);
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'departments' => $departments,
        ]);
    }

    public function getParentRoles(Request $request): JsonResponse
    {
        $request->validate([
            'organization_id' => 'nullable|exists:organizations,id',
            'department_id' => 'nullable|exists:departments,id',
            'exclude_role_id' => 'nullable|exists:roles,id',
        ]);

        $roles = Role::query()
            ->where('is_active', true)
            ->when($request->organization_id, function ($q) use ($request) {
                $q->where('organization_id', $request->organization_id);
            })
            ->when($request->department_id, function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            })
            ->when($request->exclude_role_id, function ($q) use ($request) {
                $q->where('id', '!=', $request->exclude_role_id);
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'roles' => $roles,
        ]);
    }
}
