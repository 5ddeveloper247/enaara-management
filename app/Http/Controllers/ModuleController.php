<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Services\ModuleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModuleController extends Controller
{
    public function __construct(
        private ModuleService $moduleService
    ) {}

    public function index(): View
    {
        $modules = $this->moduleService->getList();
        $counts = $this->moduleService->getCounts();

        return view('admin.module.index', [
            'modules' => $modules,
            'total' => $counts['total'],
            'active' => $counts['active'],
            'inactive' => $counts['inactive'],
        ]);
    }

    public function create(): View
    {
        $moduleCategories = $this->moduleService->getModuleCategoriesForSelect();

        return view('admin.module.create', [
            'moduleCategories' => $moduleCategories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'module_category_id' => 'nullable|exists:module_categories,ID',
            'module_name' => 'required|string|max:155',
            'route' => 'nullable|string|max:155',
            'show_in_menu' => 'boolean',
            'css_class' => 'nullable|string|max:100',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $validated['show_in_menu'] = $request->boolean('show_in_menu') ? 1 : 0;

        $this->moduleService->create($validated);

        return redirect()->route('admin.module.index')
            ->with('success', 'Module created successfully.');
    }

    public function show(int $id): View|RedirectResponse
    {
        $module = $this->moduleService->findById($id);

        if (!$module instanceof Module) {
            abort(404);
        }

        return view('admin.module.show', [
            'module' => $module,
        ]);
    }

    public function edit(int $id): View|RedirectResponse
    {
        $module = $this->moduleService->findById($id);

        if (!$module instanceof Module) {
            abort(404);
        }

        $moduleCategories = $this->moduleService->getModuleCategoriesForSelect();

        return view('admin.module.edit', [
            'module' => $module,
            'moduleCategories' => $moduleCategories,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $module = $this->moduleService->findById($id);

        if (!$module instanceof Module) {
            abort(404);
        }

        $validated = $request->validate([
            'module_category_id' => 'nullable|exists:module_categories,ID',
            'module_name' => 'required|string|max:155',
            'route' => 'nullable|string|max:155',
            'show_in_menu' => 'boolean',
            'css_class' => 'nullable|string|max:100',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $validated['show_in_menu'] = $request->boolean('show_in_menu') ? 1 : 0;

        $this->moduleService->update($module, $validated);

        return redirect()->route('admin.module.index')
            ->with('success', 'Module updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->moduleService->delete($id);

        if (!$deleted) {
            return response()->json(['success' => false, 'message' => 'Module not found.'], 404);
        }

        return response()->json(['success' => true]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate(['show_in_menu' => 'required|boolean']);
        $module = $this->moduleService->updateStatus($id, (bool) $request->input('show_in_menu'));

        if (!$module) {
            return response()->json(['success' => false, 'message' => 'Module not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'show_in_menu' => (bool) $module->show_in_menu,
        ]);
    }

    public function searchModule(Request $request): JsonResponse
    {
        $term = $request->query('term', '');
        $items = $this->moduleService->searchModule($term);

        return response()->json([
            'results' => $items->map(fn ($m) => ['id' => $m->id, 'text' => $m->module_name]),
        ]);
    }
}
