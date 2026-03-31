<?php

namespace App\Http\Controllers;

use App\Services\SbuFloorService;
use Illuminate\View\View;
use App\Models\Sbu;
use App\Http\Requests\Admin\Sbu_floor\SbuFloorStoreRequest;
use App\Http\Requests\Admin\Sbu_floor\SbuFloorUpdateRequest;

class SbuFloorsController extends Controller
{
    public function __construct(
        private SbuFloorService $sbuFloorService
    ) {}

    public function index(): View
    {
        $sbuFloors = $this->sbuFloorService->getList();
        $counts = $this->sbuFloorService->getCounts();
        $sbus = Sbu::where('is_active', 1)->orderBy('name')->get();

        return view('admin.sbu.floor.index', [
            'sbuFloors' => $sbuFloors,
            'sbus' => $sbus,
            'totalSbuFloors' => $counts['total'],
            'activeSbuFloors' => $counts['active'],
            'activePercentage' => $counts['active_percentage'],
        ]);
    }

    public function create(SbuFloorStoreRequest $request)
    {
        if (!validatePermissions('admin/sbu-floor/add')) {
            abort(403, 'Unauthorized action.');
        }

        $sbuFloors = $this->sbuFloorService->getList();
        $counts = $this->sbuFloorService->getCounts();
        $sbus = Sbu::where('is_active', 1)->orderBy('name')->get();

        return view('admin.sbu.floor.index', [
            'sbuFloors' => $sbuFloors,
            'sbus' => $sbus,
            'totalSbuFloors' => $counts['total'],
            'activeSbuFloors' => $counts['active'],
            'activePercentage' => $counts['active_percentage'],
        ]);
    }

    public function store(SbuFloorStoreRequest $request)
    {
        if (!validatePermissions('admin/sbu-floor/add')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        try {
            $this->sbuFloorService->store($request->validated());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'SBU floor created successfully.',
                ]);
            }

            return redirect()
                ->route('admin.sbu.floor.index')
                ->with('success', 'SBU floor created successfully.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create SBU floor: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create SBU floor: ' . $e->getMessage());
        }
    }

    public function show(int $id): View
    {
        $sbuFloor = $this->sbuFloorService->findById($id);

        if (!$sbuFloor) {
            abort(404);
        }

        return view('admin.sbu.floor.show', ['sbuFloor' => $sbuFloor]);
    }

    public function edit($id)
    {
        if (!validatePermissions('admin/sbu-floor/edit')) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        try {
            $sbuFloor = $this->sbuFloorService->findById($id);

            if (!$sbuFloor) {
                return response()->json([
                    'success' => false,
                    'message' => 'SBU floor not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $sbuFloor->id,
                    'sbu_id' => $sbuFloor->sbu_id,
                    'name' => $sbuFloor->name,
                    'floor_number' => $sbuFloor->floor_number,
                    'floor_type' => $sbuFloor->floor_type,
                    'is_restricted' => $sbuFloor->is_restricted,
                    'is_active' => $sbuFloor->is_active,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch SBU floor: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(SbuFloorUpdateRequest $request, $id)
    {
        if (!validatePermissions('admin/sbu-floor/edit')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        try {
            $this->sbuFloorService->update($id, $request->validated());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'SBU floor updated successfully.',
                ]);
            }

            return redirect()
                ->route('admin.sbu.floor.index')
                ->with('success', 'SBU floor updated successfully.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update SBU floor: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update SBU floor: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        if (!validatePermissions('admin/sbu-floor/delete')) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        try {
            $this->sbuFloorService->destroy($id);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'SBU floor deleted successfully.',
                ]);
            }

            return redirect()
                ->route('admin.sbu.floor.index')
                ->with('success', 'SBU floor deleted successfully.');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete SBU floor: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Failed to delete SBU floor: ' . $e->getMessage());
        }
    }
}