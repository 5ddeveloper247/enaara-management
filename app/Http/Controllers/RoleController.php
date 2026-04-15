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
use App\Models\RoleLevel;
use App\Models\Sbu;
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
        $levels= RoleLevel::orderBy('level')->get();
        $sbus= Sbu::where('is_active', true)->orderBy('name')->get();
        return view('admin.role.create', [
            'moduleCategories' => $moduleCategories,
            'organizations' => $organizations,
            'departments' => $departments,
            'parentRoles' => $parentRoles,
            'levels' => $levels,
            'sbus' => $sbus,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'level_id' => 'required|exists:role_levels,id',
            'slug' => 'nullable|string|max:255|unique:roles,slug',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'is_primary' => 'boolean',
            'organization_id' => 'required|exists:organizations,id',
            'sbu_ids' => 'nullable|array',
            'sbu_ids.*' => 'integer|exists:sbus,id',
            'department_id' => 'nullable|exists:departments,id',
            'parent_role_id' => 'nullable|exists:roles,id',
            'module_ids' => 'nullable|array',
            'module_ids.*' => 'integer|exists:modules,id',
        ]);
        $roleLevel = RoleLevel::findOrFail((int) $validated['level_id']);
        $validated['name'] = $roleLevel->name;

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_primary'] = $request->boolean('is_primary');
        $validated['sbu_ids'] = $request->input('sbu_ids', []);
        $validated['module_ids'] = $request->input('module_ids', []);
        unset($validated['level_id']);

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
        $levels = RoleLevel::orderBy('level')->get();
        $selectedLevelId = RoleLevel::where('name', $role->name)->value('id');
        $sbus = Sbu::where('is_active', true)->orderBy('name')->get();
        $selectedSbuIds = $role->sbus->pluck('id')->toArray();
        if (empty($selectedSbuIds) && $role->sbu_id) {
            $selectedSbuIds = [(int) $role->sbu_id];
        }
        $parentRoles = Role::where('is_active', true)
            ->where('id', '!=', $role->id)
            ->orderBy('name')
            ->get();
        return view('admin.role.edit', [
            'role' => $role,
            'moduleCategories' => $moduleCategories,
            'organizations' => $organizations,
            'departments' => $departments,
            'levels' => $levels,
            'selectedLevelId' => $selectedLevelId,
            'sbus' => $sbus,
            'selectedSbuIds' => $selectedSbuIds,
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
            'level_id' => 'required|exists:role_levels,id',
            'slug' => 'nullable|string|max:255|unique:roles,slug,' . $role->id,
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'is_primary' => 'boolean',
            'organization_id' => 'required|exists:organizations,id',
            'sbu_ids' => 'nullable|array',
            'sbu_ids.*' => 'integer|exists:sbus,id',
            'department_id' => 'nullable|exists:departments,id',
            'parent_role_id' => 'nullable|exists:roles,id',
            'module_ids' => 'nullable|array',
            'module_ids.*' => 'integer|exists:modules,id',
        ]);
        $roleLevel = RoleLevel::findOrFail((int) $validated['level_id']);
        $validated['name'] = $roleLevel->name;

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_primary'] = $request->boolean('is_primary');
        $validated['sbu_ids'] = $request->input('sbu_ids', []);
        $validated['module_ids'] = $request->input('module_ids', []);
        unset($validated['level_id']);
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
            'organization_id' => 'required|exists:organizations,id',
        ]);

        $sbus = Sbu::query()
            ->where('is_active', true)
            ->when($request->organization_id, function ($q) use ($request) {
                $q->where('organization_id', $request->organization_id);
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'sbus' => $sbus,
        ]);
    }

    public function getParentRoles(Request $request): JsonResponse
    {
        $request->validate([
            'organization_id' => 'nullable|exists:organizations,id',
            'level_id' => 'nullable|exists:role_levels,id',
            'sbu_id' => 'nullable|exists:sbus,id',
            'sbu_ids' => 'nullable|string',
            'exclude_role_id' => 'nullable|exists:roles,id',
        ]);

        $sbuIds = collect(explode(',', (string) $request->query('sbu_ids', '')))
            ->filter(fn ($id) => $id !== '')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();
        $selectedLevelValue = null;
        if ($request->filled('level_id')) {
            $selectedLevelValue = RoleLevel::whereKey((int) $request->level_id)->value('level');
        }
        $organizationId = $request->organization_id ? (int) $request->organization_id : null;
        $parentOrganizationId = null;
        if ($organizationId) {
            $parentOrganizationId = Organization::whereKey($organizationId)->value('parent_id');
        }

        $roleLevelByName = RoleLevel::query()
            ->selectRaw('name, MIN(level) as level')
            ->groupBy('name');

        $roles = Role::query()
            ->joinSub($roleLevelByName, 'role_level_map', function ($join) {
                $join->on('role_level_map.name', '=', 'roles.name');
            })
            ->where('roles.is_active', true)
            ->when($organizationId, function ($q) use ($organizationId, $parentOrganizationId, $sbuIds, $request, $selectedLevelValue) {
                $q->where(function ($query) use ($organizationId, $parentOrganizationId, $sbuIds, $request, $selectedLevelValue) {
                    $query->where('roles.organization_id', $organizationId)
                        ->when($selectedLevelValue !== null, function ($orgQuery) use ($selectedLevelValue) {
                            $orgQuery->where('role_level_map.level', '<', $selectedLevelValue);
                        })
                        ->when(!empty($sbuIds), function ($orgQuery) use ($sbuIds) {
                            $orgQuery->where(function ($scopeQuery) use ($sbuIds) {
                                $scopeQuery->whereIn('roles.sbu_id', $sbuIds)
                                    ->orWhereNull('roles.sbu_id');
                            });
                        })
                        ->when(empty($sbuIds) && $request->sbu_id, function ($orgQuery) use ($request) {
                            $orgQuery->where(function ($scopeQuery) use ($request) {
                                $scopeQuery->where('roles.sbu_id', $request->sbu_id)
                                    ->orWhereNull('roles.sbu_id');
                            });
                        });
                    if ($parentOrganizationId) {
                        $query->orWhere(function ($parentQuery) use ($parentOrganizationId, $selectedLevelValue) {
                            $parentQuery->where('roles.organization_id', $parentOrganizationId)
                                ->when($selectedLevelValue !== null, function ($parentLevelQuery) use ($selectedLevelValue) {
                                    $parentLevelQuery->where('role_level_map.level', '<=', $selectedLevelValue);
                                })
                                ->whereNull('roles.sbu_id');
                        });
                    }
                });
            })
            ->when($request->exclude_role_id, function ($q) use ($request) {
                $q->where('roles.id', '!=', $request->exclude_role_id);
            })
            ->orderBy('role_level_map.level')
            ->orderBy('roles.name')
            ->get(['roles.id', 'roles.name'])
            ->unique('id')
            ->values();

        return response()->json([
            'success' => true,
            'roles' => $roles,
        ]);
    }
}
