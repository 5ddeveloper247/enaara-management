<?php

namespace App\Http\Controllers;

use App\Models\RoleLevel;
use App\Services\RoleLevelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Requests\Admin\RoleLevel\RoleLevelStoreRequest;
use App\Http\Requests\Admin\RoleLevel\RoleLevelUpdateRequest;
use Illuminate\Validation\ValidationException;

class RoleLevelController extends Controller
{
    public function __construct(
        private RoleLevelService $roleLevelService
    ) {}

    public function index(): View|\Illuminate\Http\JsonResponse
    {
        $roleLevels = $this->roleLevelService->getList();
        $counts = $this->roleLevelService->getCounts();

        return view('admin.rolelevels.index', [
            'roleLevels' => $roleLevels,
            'totalRoleLevels' => $counts['total'],
            'activeRoleLevels' => $counts['active'],
            'inactiveRoleLevels' => $counts['inactive'],
            'activePercentage' => $counts['active_percentage'],
        ]);
    }

    public function store(RoleLevelStoreRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if (!validatePermissions('admin/role-levels/add')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        try {
            $validated = $request->validated();
            $roleLevel = $this->roleLevelService->create($validated);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Role Level created successfully.',
                    'roleLevel' => $roleLevel,
                ]);
            }

            return redirect()->route('admin.role-levels.index')->with('success', 'Role Level created successfully.');
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
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create Role Level. ' . $e->getMessage(),
                ], 500);
            }
            throw $e;
        }
    }

    public function edit(int $id): View|RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if (!validatePermissions('admin/role-levels/edit')) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        try {
            $roleLevel = $this->roleLevelService->findById($id);

            if (!$roleLevel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role Level not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $roleLevel->id,
                    'name' => $roleLevel->name,
                    'description' => $roleLevel->description,
                    'level' => $roleLevel->level,
                    'is_active' => $roleLevel->is_active,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Role Level: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(RoleLevelUpdateRequest $request, int $id): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if (!validatePermissions('admin/role-levels/edit')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        $roleLevel = $this->roleLevelService->findById($id);
        if (!$roleLevel) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'Role Level not found'], 404);
            }
            abort(404);
        }

        try {
            $validated = $request->validated();
            $this->roleLevelService->update($roleLevel, $validated);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Role Level updated successfully.',
                    'roleLevel' => $this->roleLevelService->findById($id),
                ]);
            }

            return redirect()->route('admin.role-levels.index')->with('success', 'Role Level updated successfully.');
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
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update Role Level. ' . $e->getMessage(),
                ], 500);
            }
            throw $e;
        }
    }

    public function destroy(int $id): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if (!validatePermissions('admin/role-levels/delete')) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        $roleLevel = $this->roleLevelService->findById($id);

        if (!$roleLevel) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role Level not found.'
                ], 404);
            }
            return redirect()->back()->with('error', 'Role Level not found.');
        }

        try {
            $this->roleLevelService->destroy($roleLevel);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Role Level deleted successfully.'
                ]);
            }

            return redirect()->route('admin.role-levels.index')->with('success', 'Role Level deleted successfully.');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete Role Level. ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to delete Role Level. ' . $e->getMessage());
        }
    }
}
