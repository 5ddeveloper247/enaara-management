<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\LogsActivity;

class Sbu extends Model
{
    use LogsActivity;
    protected $table = 'sbus';

    protected $fillable = [
        'organization_id',
        'name',
        'city',
        'address',
        'latitude',
        'longitude',
        'working_days',
        'working_start_time',
        'working_end_time',
        'opening_grace_period',
        'closing_grace_period',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'working_days' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function floors(): HasMany
    {
        return $this->hasMany(SbuFloor::class, 'sbu_id');
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class, 'sbu_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_sbu', 'sbu_id', 'role_id')->withTimestamps();
    }
}
