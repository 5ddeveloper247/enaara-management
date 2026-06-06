<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShiftRosterApprovalRequest extends Model
{
    protected $fillable = [
        'request_type',
        'employee_id',
        'outsourced_employee_id',
        'approver_employee_id',
        'requested_by',
        'start_date',
        'end_date',
        'shift_count',
        'off_day_count',
        'shift_label',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function outsourcedEmployee(): BelongsTo
    {
        return $this->belongsTo(OutsourcedEmployee::class, 'outsourced_employee_id');
    }

    public function approverEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approver_employee_id');
    }

    public function requestedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShiftRosterApprovalRequestItem::class);
    }

    public function segments(): HasMany
    {
        return $this->hasMany(ShiftRosterApprovalSegment::class, 'shift_roster_approval_request_id');
    }

    public function isPending(): bool
    {
        return $this->approval_status === 'pending';
    }
}
