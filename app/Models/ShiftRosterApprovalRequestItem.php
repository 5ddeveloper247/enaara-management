<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftRosterApprovalRequestItem extends Model
{
    protected $fillable = [
        'shift_roster_approval_request_id',
        'roster_date',
        'entry_type',
        'shift_planner_id',
        'is_custom_time',
        'start_time',
        'end_time',
        'floor',
        'location_text',
        'notes',
        'entry_status',
    ];

    protected $casts = [
        'roster_date' => 'date',
        'is_custom_time' => 'boolean',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ShiftRosterApprovalRequest::class, 'shift_roster_approval_request_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(ShiftPlanner::class, 'shift_planner_id');
    }
}
