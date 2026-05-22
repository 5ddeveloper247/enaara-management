<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;
use App\Models\User;

class ShiftRosterEntry extends Model
{
    use LogsActivity, SoftDeletes;

    protected $table = 'shift_roster_entries';

    protected $fillable = [
        'assignment_id',
        'employee_id',
        'outsourced_employee_id',
        'shift_planner_id',
        'is_custom_time',
        'roster_date',
        'start_time',
        'end_time',
        'check_in',
        'check_out',
        'floor',
        'location_text',
        'notes',
        'late_check_in',
        'status',
        'is_compensatory_earned',
        'compensatory_reason',
        'created_by',
        'updated_by',
        'assigned_by',
        'deleted_by',
    ];

    protected $casts = [
        'roster_date' => 'date',
        'late_check_in' => 'boolean',
        'is_custom_time' => 'boolean',
        'is_compensatory_earned' => 'boolean',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ShiftRosterAssignment::class, 'assignment_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function outsourcedEmployee(): BelongsTo
    {
        return $this->belongsTo(OutsourcedEmployee::class, 'outsourced_employee_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(ShiftPlanner::class, 'shift_planner_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function events(): HasMany
    {
        return $this->hasMany(ShiftRosterEntryEvent::class, 'shift_roster_entry_id');
    }
}

