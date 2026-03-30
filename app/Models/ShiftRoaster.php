<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShiftRoaster extends Model
{
    use SoftDeletes;

    protected $table = 'shift_rosters';

    protected $fillable = [
        'employee_id',
        'shift_planner_id',
        'roster_date',
        'status',
        'notes',
        'assigned_by',
        'updated_by',
    ];

    /**
     * Casting
     */
    protected $casts = [
        'roster_date' => 'date',
        'status' => 'integer',
    ];

    /**
     * =========================
     * Relationships
     * =========================
     */

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift()
    {
        return $this->belongsTo(ShiftPlanner::class, 'shift_planner_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * =========================
     * Scopes (very useful)
     * =========================
     */

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('roster_date', $date);
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('roster_date', [$start, $end]);
    }

    /**
     * =========================
     * Accessors (optional but useful)
     * =========================
     */

    public function getFormattedDateAttribute()
    {
        return $this->roster_date?->format('d M Y');
    }

    public function getStatusLabelAttribute()
    {
        return $this->status == 1 ? 'Assigned' : 'Cancelled';
    }
}