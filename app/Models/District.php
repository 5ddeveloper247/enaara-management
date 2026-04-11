<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class District extends Model
{
    protected $table = 'districts';

    protected $fillable = [
        'country_id',
        'province_id',
        'name',
        'code',
        'external_city_id',
        'is_active',
    ];

    /**
     * Get the country that owns the district.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the province that owns the district.
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }
}
