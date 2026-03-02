<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkModel extends Model
{
    protected $table = 'work_models';

    protected $fillable = [
        'organization_id',
        'name',
        'code',
        'default_schedule_json',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
