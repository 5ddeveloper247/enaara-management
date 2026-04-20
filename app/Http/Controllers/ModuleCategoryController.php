<?php

namespace App\Http\Controllers;

use App\Models\ModuleCategory;
use App\Services\ModuleCategoryService;
use App\Http\Requests\Admin\ModuleCategory\ModuleCategoryStoreRequest;
use App\Http\Requests\Admin\ModuleCategory\ModuleCategoryUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ModuleCategoryController extends Controller
{
    public function __construct(
        private ModuleCategoryService $moduleCategoryService
    ) {}

    private function denyIfUnauthorized(string|array $permission, bool $expectsJson = false): ?JsonResponse
    {
        $permissions = is_array($permission) ? $permission : [$permission];
        foreach ($permissions as $permissionKey) {
            if (validatePermissions($permissionKey)) {
                return null;
            }
        }

        if ($expectsJson) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        abort(403, 'Unauthorized action.');
    }

    public function index(): View
    {
        $this->denyIfUnauthorized('admin/module-categories');

        $moduleCategories = $this->moduleCategoryService->getList();
        $counts = $this->moduleCategoryService->getCounts();

        return view('admin.module-categories.index', [
            'moduleCategories' => $moduleCategories,
            'total' => $counts['total'],
            'active' => $counts['active'],
            'inactive' => $counts['inactive'],
        ]);
    }

    public function create(): View
    {
        $this->denyIfUnauthorized(['admin/module-categories', 'admin/module-categories/add']);

        return view('admin.module-categories.create');
    }

    public function store(ModuleCategoryStoreRequest $request): RedirectResponse
    {
        $this->denyIfUnauthorized(['admin/module-categories', 'admin/module-categories/add']);

        try {
            $validated = $request->validated();
            $validated['is_active'] = $request->boolean('is_active');

            $this->moduleCategoryService->create($validated);

            return redirect()->route('admin.module.category.index')
                ->with('success', 'Module category created successfully.');
        } catch (\Exception $e) {
            Log::error('Module category create failed', [
                'exception' => $e->getMessage(),
            ]);
            return redirect()->back()->withInput()->with('error', 'Failed to create module category.');
        }
    }

    public function show(int $id): View|RedirectResponse
    {
        $this->denyIfUnauthorized('admin/module-categories');

        $moduleCategory = $this->moduleCategoryService->findById($id);

        if (!$moduleCategory instanceof ModuleCategory) {
            abort(404);
        }

        return view('admin.module-categories.show', [
            'moduleCategory' => $moduleCategory,
        ]);
    }

    public function edit(int $id): View|RedirectResponse
    {
        $this->denyIfUnauthorized(['admin/module-categories', 'admin/module-categories/edit']);

        $moduleCategory = $this->moduleCategoryService->findById($id);

        if (!$moduleCategory instanceof ModuleCategory) {
            abort(404);
        }

        return view('admin.module-categories.edit', [
            'moduleCategory' => $moduleCategory,
        ]);
    }

    public function update(ModuleCategoryUpdateRequest $request, int $id): RedirectResponse
    {
        $this->denyIfUnauthorized(['admin/module-categories', 'admin/module-categories/edit']);

        $moduleCategory = $this->moduleCategoryService->findById($id);

        if (!$moduleCategory instanceof ModuleCategory) {
            abort(404);
        }

        try {
            $validated = $request->validated();
            $validated['is_active'] = $request->boolean('is_active');

            $this->moduleCategoryService->update($moduleCategory, $validated);

            return redirect()->route('admin.module.category.index')
                ->with('success', 'Module category updated successfully.');
        } catch (\Exception $e) {
            Log::error('Module category update failed', [
                'module_category_id' => $id,
                'exception' => $e->getMessage(),
            ]);
            return redirect()->back()->withInput()->with('error', 'Failed to update module category.');
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $denied = $this->denyIfUnauthorized(['admin/module-categories', 'admin/module-categories/delete'], true);
        if ($denied instanceof JsonResponse) {
            return $denied;
        }

        try {
            $moduleCategory = $this->moduleCategoryService->findById($id);
            if (!$moduleCategory instanceof ModuleCategory) {
                return response()->json(['success' => false, 'message' => 'Module category not found.'], 404);
            }

            if ($moduleCategory->modules()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete this module category because modules are linked to it.',
                ], 422);
            }

            $deleted = $this->moduleCategoryService->delete($id);

            if (!$deleted) {
                return response()->json(['success' => false, 'message' => 'Module category not found.'], 404);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Module category delete failed', [
                'module_category_id' => $id,
                'exception' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete module category.',
            ], 500);
        }
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $denied = $this->denyIfUnauthorized(['admin/module-categories', 'admin/module-categories/edit'], true);
        if ($denied instanceof JsonResponse) {
            return $denied;
        }

        $request->validate(['is_active' => 'required|boolean']);

        try {
            $category = $this->moduleCategoryService->updateStatus($id, (bool) $request->input('is_active'));

            if (!$category) {
                return response()->json(['success' => false, 'message' => 'Module category not found.'], 404);
            }

            return response()->json([
                'success' => true,
                'is_active' => $category->is_active,
            ]);
        } catch (\Exception $e) {
            Log::error('Module category status update failed', [
                'module_category_id' => $id,
                'exception' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update module category status.',
            ], 500);
        }
    }

    public function searchModuleCategory(Request $request): JsonResponse
    {
        $denied = $this->denyIfUnauthorized('admin/module-categories', true);
        if ($denied instanceof JsonResponse) {
            return $denied;
        }

        $term = $request->query('term', '');
        $items = $this->moduleCategoryService->searchModuleCategory($term);

        return response()->json([
            'results' => $items->map(fn ($c) => ['id' => $c->ID, 'text' => $c->category_name]),
        ]);
    }
}
