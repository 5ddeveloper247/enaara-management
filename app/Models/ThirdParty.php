<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ThirdParty extends Model
{
    use LogsActivity;

    protected $table = 'third_parties';

    protected $fillable = [
        'organization_id',
        'third_party_name',
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

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'third_party_organizations')
            ->withTimestamps();
    }

    public function sbus(): BelongsToMany
    {
        return $this->belongsToMany(Sbu::class, 'third_party_sbu')
            ->withTimestamps();
    }
}
