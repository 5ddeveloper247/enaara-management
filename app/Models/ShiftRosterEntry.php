<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\LogsActivity;

class ShiftRosterEntry extends Model
{
    use LogsActivity;

    protected $table = 'shift_roster_entries';

    protected $fillable = [
        'assignment_id',
        'employee_id',
        'shift_planner_id',
        'roster_date',
        'start_time',
        'end_time',
        'check_in',
        'check_out',
        'floor',
        'late_check_in',
        'status',
        'is_compensatory_earned',
    ];

    protected $casts = [
        'roster_date' => 'date',
        'late_check_in' => 'boolean',
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

    public function shift(): BelongsTo
    {
        return $this->belongsTo(ShiftPlanner::class, 'shift_planner_id');
    }
}
