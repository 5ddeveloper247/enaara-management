<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeType extends Model
{
    protected $table = 'employee_types';

    protected $fillable = [
        'organization_id',
        'name',
        'code',
        'rules_json',
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
