<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveTypeEncashmentRule extends Model
{
    protected $fillable = [
        'leave_type_id',
        'service_months',
        'role_level_id',
        'max_forward_days',
    ];

    protected $casts = [
        'service_months' => 'integer',
        'max_forward_days' => 'decimal:2',
    ];

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function roleLevel(): BelongsTo
    {
        return $this->belongsTo(RoleLevel::class);
    }
}
