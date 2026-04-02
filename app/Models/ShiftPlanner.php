<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\LogsActivity;

class ShiftPlanner extends Model
{
    use LogsActivity;
    use SoftDeletes;

    protected $table = 'shift_planners';

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'name',
        'code',
        'start_time',
        'end_time',
        'clock_in_window_minutes',
        'clock_out_window_minutes',
        'shift_duration_minutes',
        'grace_period_minutes',
        'break_time_minutes',
        'overtime_allowed',
        'overtime_trigger_hours',
        'is_active',
        'created_by',
        'updated_by',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'overtime_allowed' => 'boolean',
        'is_active' => 'boolean',
        'clock_in_window_minutes' => 'integer',
        'clock_out_window_minutes' => 'integer',
        'shift_duration_minutes' => 'integer',
        'grace_period_minutes' => 'integer',
        'break_time_minutes' => 'integer',
        'overtime_trigger_hours' => 'float',
    ];

    /**
     * Relationships
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}