<?php

namespace App\Http\Controllers;

use App\Models\ModuleCategory;
use App\Services\ModuleCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModuleCategoryController extends Controller
{
    public function __construct(
        private ModuleCategoryService $moduleCategoryService
    ) {}

    public function index(): View
    {
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
        return view('admin.module-categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:155',
            'css_class' => 'nullable|string|max:100',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $this->moduleCategoryService->create($validated);

        return redirect()->route('admin.module.category.index')
            ->with('success', 'Module category created successfully.');
    }

    public function show(int $id): View|RedirectResponse
    {
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
        $moduleCategory = $this->moduleCategoryService->findById($id);

        if (!$moduleCategory instanceof ModuleCategory) {
            abort(404);
        }

        return view('admin.module-categories.edit', [
            'moduleCategory' => $moduleCategory,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $moduleCategory = $this->moduleCategoryService->findById($id);

        if (!$moduleCategory instanceof ModuleCategory) {
            abort(404);
        }

        $validated = $request->validate([
            'category_name' => 'required|string|max:155',
            'css_class' => 'nullable|string|max:100',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $this->moduleCategoryService->update($moduleCategory, $validated);

        return redirect()->route('admin.module.category.index')
            ->with('success', 'Module category updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->moduleCategoryService->delete($id);

        if (!$deleted) {
            return response()->json(['success' => false, 'message' => 'Module category not found.'], 404);
        }

        return response()->json(['success' => true]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate(['is_active' => 'required|boolean']);
        $category = $this->moduleCategoryService->updateStatus($id, (bool) $request->input('is_active'));

        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Module category not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'is_active' => $category->is_active,
        ]);
    }

    public function searchModuleCategory(Request $request): JsonResponse
    {
        $term = $request->query('term', '');
        $items = $this->moduleCategoryService->searchModuleCategory($term);

        return response()->json([
            'results' => $items->map(fn ($c) => ['id' => $c->ID, 'text' => $c->category_name]),
        ]);
    }
}
