<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\BiometricDevice\BiometricDeviceStoreRequest;
use App\Http\Requests\Admin\BiometricDevice\BiometricDeviceUpdateRequest;
use App\Models\Organization;
use App\Models\Sbu;
use App\Services\BiometricDeviceService;
use Illuminate\View\View;

class BiometricDeviceController extends Controller
{
    public function __construct(
        private BiometricDeviceService $biometricDeviceService
    ) {}

    public function index(): View
    {
        $devices = $this->biometricDeviceService->getList();
        $counts = $this->biometricDeviceService->getCounts();
        $organizations = Organization::query()->where('is_active', true)->orderBy('name')->get();
        $sbus = Sbu::query()
            ->select(['id', 'name', 'organization_id'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.biometric-device.index', [
            'devices' => $devices,
            'organizations' => $organizations,
            'sbus' => $sbus,
            'totalDevices' => $counts['total'],
            'activeDevices' => $counts['active'],
            'inactiveDevices' => $counts['inactive'],
            'faultyDevices' => $counts['faulty'],
            'activePercentage' => $counts['active_percentage'],
        ]);
    }

    public function create()
    {
        if (! validatePermissions('admin/biometric-device/add')) {
            abort(403, 'Unauthorized action.');
        }

        return $this->index();
    }

    public function store(BiometricDeviceStoreRequest $request)
    {
        if (! validatePermissions('admin/biometric-device/add')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.',
                ], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        try {
            $this->biometricDeviceService->store($request->validated());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Biometric device registered successfully.',
                ]);
            }

            return redirect()
                ->route('admin.biometric-device.index')
                ->with('success', 'Biometric device registered successfully.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to register device: '.$e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to register device: '.$e->getMessage());
        }
    }

    public function show(int $id): View
    {
        $device = $this->biometricDeviceService->findById($id);

        if (! $device) {
            abort(404);
        }

        return view('admin.biometric-device.show', ['device' => $device]);
    }

    public function edit($id)
    {
        if (! validatePermissions('admin/biometric-device/edit')) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.',
                ], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        try {
            $device = $this->biometricDeviceService->findById((int) $id);

            if (! $device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $device->id,
                    'organization_id' => $device->organization_id,
                    'sbu_id' => $device->sbu_id,
                    'device_name' => $device->device_name,
                    'serial_number' => $device->serial_number,
                    'device_type' => $device->device_type,
                    'brand_model' => $device->brand_model,
                    'ip_address' => $device->ip_address,
                    'port' => $device->port,
                    'connection_type' => $device->connection_type,
                    'device_status' => $device->device_status,
                    'installation_date' => $device->installation_date?->format('Y-m-d'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch device: '.$e->getMessage(),
            ], 500);
        }
    }

    public function update(BiometricDeviceUpdateRequest $request, $id)
    {
        if (! validatePermissions('admin/biometric-device/edit')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.',
                ], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        try {
            $this->biometricDeviceService->update((int) $id, $request->validated());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Biometric device updated successfully.',
                ]);
            }

            return redirect()
                ->route('admin.biometric-device.index')
                ->with('success', 'Biometric device updated successfully.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update device: '.$e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update device: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        if (! validatePermissions('admin/biometric-device/delete')) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.',
                ], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        try {
            $this->biometricDeviceService->destroy((int) $id);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Biometric device removed successfully.',
                ]);
            }

            return redirect()
                ->route('admin.biometric-device.index')
                ->with('success', 'Biometric device removed successfully.');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete device: '.$e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Failed to delete device: '.$e->getMessage());
        }
    }
}
