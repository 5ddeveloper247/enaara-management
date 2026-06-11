<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

use App\Traits\LogsActivity;

class LeaveType extends Model
{
    use LogsActivity;
    protected $table = 'leave_types';

    protected $fillable = [
        'organization_id',
        'sbu_id',
        'name',
        'leave_condition',
        'code',
        'leave_category',
        'description',
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

    public function departments(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'leave_type_department');
    }

    public function sbus(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Sbu::class, 'leave_type_sbu');
    }

    public function sbu(): BelongsTo
    {
        return $this->belongsTo(Sbu::class);
    }

    public function setting(): HasOne
    {
        return $this->hasOne(LeaveTypeSetting::class);
    }

    public function encashmentRules(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LeaveTypeEncashmentRule::class);
    }
}

