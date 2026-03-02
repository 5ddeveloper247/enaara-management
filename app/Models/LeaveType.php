<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveType extends Model
{
    protected $table = 'leave_types';

    protected $fillable = [
        'organization_id',
        'name',
        'code',
        'annual_quota',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'annual_quota' => 'decimal:2',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}

