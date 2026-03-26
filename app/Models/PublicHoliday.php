<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicHoliday extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_recurring',
        'organization_scope',
        'is_blackout',
        'reason',
    ];

    protected $casts = [
        'is_recurring' => 'boolean',
        'is_blackout' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * The organizations that are associated with the public holiday.
     */
    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'holiday_organization', 'public_holiday_id', 'organization_id');
    }
}
