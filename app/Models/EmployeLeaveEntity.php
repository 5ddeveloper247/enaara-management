<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeLeaveEntity extends Model
{
    protected $table = 'employe_leave_entities';

    protected $fillable = [
        'leave_request_id',
        'employee_id',
        'leave_type_id',
        'department_id',
        'leave_date',
        'start_date',
        'end_date',
        'duration',
        'half_day_session',
        'status',
    ];

    protected $casts = [
        'leave_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'duration' => 'decimal:2',
        'status' => 'integer',
    ];

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(EmployeLeaveRequest::class, 'leave_request_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }
}

