<?php
namespace App\Services;
use App\Models\Geofence;
class DashboardService{
    public function index()
    {
        $geofences = Geofence::with('sbu')->orderBy('name')->get();
        // dd($geofences);
        return view('admin.dashboard.index', compact('geofences'));
    }
}