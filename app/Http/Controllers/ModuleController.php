<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Services\ModuleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ModuleController extends Controller
{
    public function __construct(
        private ModuleService $moduleService
    ) {}

    public function index(): View|\Illuminate\Http\JsonResponse
    {
        if (!validatePermissions('admin/module')) {
            if (request()->expectsJson() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        $modules = $this->moduleService->getList();
        $counts = $this->moduleService->getCounts();

        return view('admin.module.index', [
            'modules' => $modules,
            'total' => $counts['total'],
            'active' => $counts['active'],
            'inactive' => $counts['inactive'],
        ]);
    }

    public function create(): View|\Illuminate\Http\JsonResponse
    {
        if (!validatePermissions('admin/module/add')) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        $moduleCategories = $this->moduleService->getModuleCategoriesForSelect();

        return view('admin.module.create', [
            'moduleCategories' => $moduleCategories,
        ]);
    }

    public function store(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if (!validatePermissions('admin/module/add')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'module_category_id' => 'required|exists:module_categories,ID',
            'module_name' => ['required', 'string', 'max:155', Rule::unique('modules', 'module_name')],
            'route' => 'nullable|string|max:155',
            'show_in_menu' => 'boolean',
            'css_class' => 'nullable|string|max:100',
            'display_order' => ['required', 'integer', 'min:0', Rule::unique('modules', 'display_order')],
        ]);

        $validated['show_in_menu'] = $request->boolean('show_in_menu') ? 1 : 0;

        $this->moduleService->create($validated);

        return redirect()->route('admin.module.index')
            ->with('success', 'Module created successfully.');
    }

    public function show(int $id): View|RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if (!validatePermissions('admin/module')) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        $module = $this->moduleService->findById($id);

        if (!$module instanceof Module) {
            abort(404);
        }

        return view('admin.module.show', [
            'module' => $module,
        ]);
    }

    public function edit(int $id): View|RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if (!validatePermissions('admin/module/edit/{id}')) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

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

    public function update(Request $request, int $id): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if (!validatePermissions('admin/module/edit/{id}')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        $module = $this->moduleService->findById($id);

        if (!$module instanceof Module) {
            abort(404);
        }

        $validated = $request->validate([
            'module_category_id' => 'nullable|exists:module_categories,ID',
            'module_name' => ['required', 'string', 'max:155', Rule::unique('modules', 'module_name')->ignore($id)],
            'route' => 'nullable|string|max:155',
            'show_in_menu' => 'boolean',
            'css_class' => 'nullable|string|max:100',
            'display_order' => ['required', 'integer', 'min:0', Rule::unique('modules', 'display_order')->ignore($id)],
        ]);

        $validated['show_in_menu'] = $request->boolean('show_in_menu') ? 1 : 0;

        $this->moduleService->update($module, $validated);

        return redirect()->route('admin.module.index')
            ->with('success', 'Module updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        if (!validatePermissions('admin/module/{id}/delete')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $deleted = $this->moduleService->delete($id);

        if (!$deleted) {
            return response()->json(['success' => false, 'message' => 'Module not found.'], 404);
        }

        return response()->json(['success' => true]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        if (!validatePermissions('admin/module/edit/{id}')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

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
        if (!validatePermissions('admin/module')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $term = $request->query('term', '');
        $items = $this->moduleService->searchModule($term);

        return response()->json([
            'results' => $items->map(fn ($m) => ['id' => $m->id, 'text' => $m->module_name]),
        ]);
    }
}
