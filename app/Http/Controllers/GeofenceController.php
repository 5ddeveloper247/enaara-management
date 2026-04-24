<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sbu;
use App\Models\Geofence;
use App\Models\Organization;
use App\Services\GeofenceService;
use App\Http\Requests\Admin\Geofencing\StoreGeofenceRequest;
use Illuminate\Validation\ValidationException;

class GeofenceController extends Controller
{
    private GeofenceService $geofenceService;

    public function __construct(GeofenceService $geofenceService)
    {
        $this->geofenceService = $geofenceService;
    }

    public function index()
    {
        // if(!validatePermissions('admin/geofencing/list')){
        //    abort(403, 'Unauthorized action.');
        // }

        $organizations = Organization::query()->where('is_active', true)->orderBy('name')->get();
        if ($organizations->isEmpty()) {
            $organizations = Organization::query()->orderBy('name')->get();
        }

        $sbus = Sbu::where('is_active', true)->orderBy('name')->get();
        // Fallback if sbus don't have is_active or need specific fetching
        if ($sbus->isEmpty()) {
            $sbus = Sbu::orderBy('name')->get();
        }

        $geofences = Geofence::with(['sbu', 'organization'])->orderBy('name')->get();
        $totalFences = $geofences->count();

        return view('admin.geofencing.index', compact('organizations', 'sbus', 'geofences', 'totalFences'));
    }

    public function store(StoreGeofenceRequest $request)
    {
        // if(!validatePermissions('admin/geofencing/add')){
        //    abort(403, 'Unauthorized action.');
        // }

        try {
            $geofence = $this->geofenceService->store($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Geofence created successfully.',
                'geofence' => $geofence->load('sbu')
            ], 201);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create geofence: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        // if(!validatePermissions('admin/geofencing/edit')){
        //    abort(403, 'Unauthorized action.');
        // }

        $geofence = Geofence::with('sbu')->findOrFail($id);
        $organizationId = $geofence->organization_id ?? $geofence->sbu?->organization_id;
        
        return response()->json([
            'success' => true,
            'geofence' => array_merge($geofence->toArray(), [
                'organization_id' => $organizationId,
            ]),
        ]);
    }

    public function update(\App\Http\Requests\Admin\Geofencing\UpdateGeofenceRequest $request, $id)
    {
        // if(!validatePermissions('admin/geofencing/edit')){
        //    abort(403, 'Unauthorized action.');
        // }

        try {
            $geofence = Geofence::findOrFail($id);
            $updatedGeofence = $this->geofenceService->update($geofence, $request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Geofence updated successfully.',
                'geofence' => $updatedGeofence->load('sbu')
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update geofence: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        // if(!validatePermissions('admin/geofencing/delete')){
        //    abort(403, 'Unauthorized action.');
        // }

        try {
            $geofence = Geofence::findOrFail($id);
            $this->geofenceService->destroy($geofence);
            
            return response()->json([
                'success' => true,
                'message' => 'Geofence deleted successfully.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete geofence: ' . $e->getMessage()
            ], 500);
        }
    }
}
