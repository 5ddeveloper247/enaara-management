<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\LogsActivity;

class Geofence extends Model
{
    use LogsActivity;
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'radius',
        'radius_unit',
        'type',
        'organization_id',
        'sbu_id',
        'anti_spoofing',
        'offline_sync',
        'auto_check_in',
        'status',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'radius' => 'integer',
        'anti_spoofing' => 'boolean',
        'offline_sync' => 'boolean',
        'auto_check_in' => 'boolean',
    ];

    public function sbu()
    {
        return $this->belongsTo(Sbu::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
