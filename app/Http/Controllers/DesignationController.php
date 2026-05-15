<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\Designation\DesignationStoreRequest;
use App\Http\Requests\Admin\Designation\DesignationUpdateRequest;
use App\Services\DesignationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DesignationController extends Controller
{
    public function __construct(
        private DesignationService $designationService
    ) {}

    public function index(): View|\Illuminate\Http\JsonResponse
    {
        $designations = $this->designationService->getList();
        $counts = $this->designationService->getCounts();
        $organizations = $this->designationService->getOrganizationHierarchy();

        return view('admin.designations.index', [
            'designations' => $designations,
            'organizations' => $organizations,
            'totalDesignations' => $counts['total'],
            'activeDesignations' => $counts['active'],
            'inactiveDesignations' => $counts['inactive'],
            'activePercentage' => $counts['active_percentage'],
        ]);
    }

    public function store(DesignationStoreRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if (!validatePermissions('admin/designations/add')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.',
                ], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        try {
            $validated = $request->validated();
            $designation = $this->designationService->create($validated);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Designation created successfully.',
                    'designation' => $designation,
                ]);
            }

            return redirect()->route('admin.designations.index')->with('success', 'Designation created successfully.');
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
            Log::error('Designation create failed', [
                'exception' => $e->getMessage(),
            ]);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create designation.',
                ], 500);
            }

            return redirect()->back()->withInput()->with('error', 'Failed to create designation.');
        }
    }

    public function edit(int $id): View|RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if (!validatePermissions('admin/designations/edit')) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.',
                ], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        try {
            $designation = $this->designationService->findById($id);

            if (!$designation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Designation not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $designation->id,
                    'organization_id' => $designation->sbu?->organization_id,
                    'sbu_id' => $designation->sbu_id,
                    'name' => $designation->name,
                    'description' => $designation->description,
                    'is_active' => $designation->is_active,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Designation fetch failed', [
                'designation_id' => $id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch designation.',
            ], 500);
        }
    }

    public function update(DesignationUpdateRequest $request, int $id): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if (!validatePermissions('admin/designations/edit')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.',
                ], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        $designation = $this->designationService->findById($id);
        if (!$designation) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'Designation not found'], 404);
            }
            abort(404);
        }

        try {
            $validated = $request->validated();
            $this->designationService->update($designation, $validated);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Designation updated successfully.',
                    'designation' => $this->designationService->findById($id),
                ]);
            }

            return redirect()->route('admin.designations.index')->with('success', 'Designation updated successfully.');
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
            Log::error('Designation update failed', [
                'designation_id' => $id,
                'exception' => $e->getMessage(),
            ]);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update designation.',
                ], 500);
            }

            return redirect()->back()->withInput()->with('error', 'Failed to update designation.');
        }
    }

    public function destroy(int $id): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if (!validatePermissions('admin/designations/delete')) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.',
                ], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        $designation = $this->designationService->findById($id);

        if (!$designation) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Designation not found.',
                ], 404);
            }

            return redirect()->back()->with('error', 'Designation not found.');
        }

        try {
            $this->designationService->destroy($designation);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Designation deleted successfully.',
                ]);
            }

            return redirect()->route('admin.designations.index')->with('success', 'Designation deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Designation delete failed', [
                'designation_id' => $id,
                'exception' => $e->getMessage(),
            ]);
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete designation.',
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to delete designation.');
        }
    }
}
