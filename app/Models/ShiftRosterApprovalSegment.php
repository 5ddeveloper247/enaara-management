<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShiftRosterApprovalSegment extends Model
{
    protected $fillable = [
        'shift_roster_approval_request_id',
        'department_id',
        'approver_employee_id',
        'submitted_by_user_id',
        'shift_count',
        'off_day_count',
        'employee_count',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ShiftRosterApprovalRequest::class, 'shift_roster_approval_request_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function approverEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approver_employee_id');
    }

    public function submittedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(ShiftRosterEntry::class, 'shift_roster_approval_segment_id');
    }

    public function isPending(): bool
    {
        return $this->approval_status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }
}
