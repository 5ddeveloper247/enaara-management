<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sbu;
use App\Models\Geofence;
use App\Services\GeofenceService;
use App\Http\Requests\Admin\Geofencing\StoreGeofenceRequest;

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

        $sbus = Sbu::where('is_active', true)->get();
        // Fallback if sbus don't have is_active or need specific fetching
        if ($sbus->isEmpty()) {
            $sbus = Sbu::all();
        }

        $geofences = Geofence::with('sbu')->orderBy('name')->get();

        return view('admin.geofencing.index', compact('sbus', 'geofences'));
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

        $geofence = Geofence::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'geofence' => $geofence
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
