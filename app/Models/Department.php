<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $table = 'departments';

    protected $fillable = [
        'organization_id',
        'sbu_id',
        'name',
        'code',
        'parent_department_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function sbu(): BelongsTo
    {
        return $this->belongsTo(Sbu::class, 'sbu_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_department_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_department_id');
    }
}
