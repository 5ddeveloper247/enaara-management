<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\Sbu_floor\SbuFloorStoreRequest;
use App\Http\Requests\Admin\Sbu_floor\SbuFloorUpdateRequest;
use App\Models\BiometricDevice;
use App\Services\EmployeeViewerScopeService;
use App\Services\SbuFloorService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SbuFloorsController extends Controller
{
    public function __construct(
        private SbuFloorService $sbuFloorService,
        private EmployeeViewerScopeService $viewerScope,
    ) {}

    public function index(): View
    {
        return view('admin.sbu.floor.index', $this->indexViewData());
    }

    public function create(SbuFloorStoreRequest $request)
    {
        if (! validatePermissions('admin/sbu-floor/add')) {
            abort(403, 'Unauthorized action.');
        }

        return view('admin.sbu.floor.index', $this->indexViewData());
    }

    public function store(SbuFloorStoreRequest $request)
    {
        if (! validatePermissions('admin/sbu-floor/add')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.',
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
                    'message' => 'Failed to create SBU floor: '.$e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create SBU floor: '.$e->getMessage());
        }
    }

    public function show(int $id): View
    {
        $sbuFloor = $this->sbuFloorService->findById($id);

        if (! $sbuFloor) {
            abort(404);
        }

        return view('admin.sbu.floor.show', ['sbuFloor' => $sbuFloor]);
    }

    public function detailJson(int $id): JsonResponse
    {
        try {
            $sbuFloor = $this->sbuFloorService->findById($id);

            if (! $sbuFloor) {
                return response()->json([
                    'success' => false,
                    'message' => 'SBU floor not found.',
                ], 404);
            }

            $devices = $sbuFloor->biometricDevices->map(function ($d) {
                return [
                    'id' => $d->id,
                    'device_name' => $d->device_name,
                    'serial_number' => $d->serial_number,
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $sbuFloor->id,
                    'name' => $sbuFloor->name,
                    'floor_number' => $sbuFloor->floor_number,
                    'floor_type' => $sbuFloor->floor_type,
                    'is_restricted' => $sbuFloor->is_restricted,
                    'is_active' => $sbuFloor->is_active,
                    'organization_name' => $sbuFloor->sbu?->organization?->name,
                    'sbu_name' => $sbuFloor->sbu?->name,
                    'biometric_devices' => $devices,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load floor details.',
            ], 500);
        }
    }

    public function edit($id)
    {
        if (! validatePermissions('admin/sbu-floor/edit')) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.',
                ], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        try {
            $sbuFloor = $this->sbuFloorService->findById($id);

            if (! $sbuFloor) {
                return response()->json([
                    'success' => false,
                    'message' => 'SBU floor not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $sbuFloor->id,
                    'organization_id' => $sbuFloor->sbu?->organization_id,
                    'sbu_id' => $sbuFloor->sbu_id,
                    'name' => $sbuFloor->name,
                    'floor_number' => $sbuFloor->floor_number,
                    'floor_type' => $sbuFloor->floor_type,
                    'is_restricted' => $sbuFloor->is_restricted,
                    'is_active' => $sbuFloor->is_active,
                    'biometric_device_ids' => BiometricDevice::query()
                        ->where('sbu_floor_id', $sbuFloor->id)
                        ->orderByDesc('id')
                        ->pluck('id')
                        ->values()
                        ->all(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch SBU floor: '.$e->getMessage(),
            ], 500);
        }
    }

    public function update(SbuFloorUpdateRequest $request, $id)
    {
        if (! validatePermissions('admin/sbu-floor/edit')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.',
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
                    'message' => 'Failed to update SBU floor: '.$e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update SBU floor: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        if (! validatePermissions('admin/sbu-floor/delete')) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.',
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
                    'message' => 'Failed to delete SBU floor: '.$e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Failed to delete SBU floor: '.$e->getMessage());
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function indexViewData(): array
    {
        $counts = $this->sbuFloorService->getCounts();

        return [
            'sbuFloors' => $this->sbuFloorService->getList(),
            'organizations' => $this->sbuFloorService->getOrganizationsForFilter(),
            'sbus' => $this->sbuFloorService->getSbusForFilter(),
            'biometricDevicesForFloors' => $this->sbuFloorService->getBiometricDevicesForForms(),
            'totalSbuFloors' => $counts['total'],
            'activeSbuFloors' => $counts['active'],
            'activePercentage' => $counts['active_percentage'],
            'viewerEmployeeScope' => $this->viewerScope->frontendScopePayload(),
        ];
    }
}
