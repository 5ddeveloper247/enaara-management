<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SbuFloor extends Model
{
    protected $table = 'sbu_floors';

    protected $fillable = [
        'sbu_id',
        'name',
        'floor_number',
        'floor_type',
        'is_restricted',
        'is_active',
    ];

    protected $casts = [
        'is_restricted' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function sbu(): BelongsTo
    {
        return $this->belongsTo(Sbu::class, 'sbu_id');
    }
}
