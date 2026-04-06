<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;

class ShiftRosterAssignment extends Model
{
    use LogsActivity;

    protected $table = 'shift_roster_assignments';

    protected $fillable = [
        'shift_planner_id',
        'start_date',
        'end_date',
        'days',
        'assign_mode',
        'check_conflicts',
        'override_existing',
        'exclude_weekends',
        'status',
        'error_message',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'days' => 'array',
        'check_conflicts' => 'boolean',
        'override_existing' => 'boolean',
        'exclude_weekends' => 'boolean',
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(ShiftPlanner::class, 'shift_planner_id');
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'shift_roster_assignment_employee', 'assignment_id', 'employee_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(ShiftRosterEntry::class, 'assignment_id');
    }
}
