<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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
}
