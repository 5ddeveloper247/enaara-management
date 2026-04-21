<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiometricDevice extends Model
{
    use LogsActivity;

    protected $fillable = [
        'organization_id',
        'sbu_id',
        'sbu_floor_id',
        'device_name',
        'serial_number',
        'device_type',
        'brand_model',
        'ip_address',
        'port',
        'connection_type',
        'device_status',
        'online_status',
        'last_sync_time',
        'installation_date',
        'created_by',
    ];

    protected $casts = [
        'port' => 'integer',
        'installation_date' => 'date',
        'last_sync_time' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function sbu(): BelongsTo
    {
        return $this->belongsTo(Sbu::class, 'sbu_id');
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(SbuFloor::class, 'sbu_floor_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
