<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Designation extends Model
{
    protected $table = 'designations';

    protected $fillable = [
        'organization_id',
        'sbu_id',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function sbu(): BelongsTo
    {
        return $this->belongsTo(Sbu::class, 'sbu_id');
    }
}
