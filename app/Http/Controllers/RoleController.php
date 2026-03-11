<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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

        return view('admin.role.create', [
            'moduleCategories' => $moduleCategories,
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

        return view('admin.role.edit', [
            'role' => $role,
            'moduleCategories' => $moduleCategories,
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
            'module_ids' => 'nullable|array',
            'module_ids.*' => 'integer|exists:modules,id',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_primary'] = $request->boolean('is_primary');
        $validated['module_ids'] = $request->input('module_ids', []);

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
            'results' => $items->map(fn ($r) => ['id' => $r->id, 'text' => $r->name]),
        ]);
    }
}
