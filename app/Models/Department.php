<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\LogsActivity;

class Department extends Model
{
    use LogsActivity;
    protected $table = 'departments';

    protected $fillable = [
        'organization_id',
        'sbu_id',
        'name',
        'code',
        'parent_department_id',
        'description',
        'working_days',
        'working_start_time',
        'working_end_time',
        'opening_grace_period',
        'closing_grace_period',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'working_days' => 'array',
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
