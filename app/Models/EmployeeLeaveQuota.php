<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeaveQuota extends Model
{
    protected $table = 'employee_leave_quotas';

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'department_id',
        'year',
        'quota',
        'used',
    ];

    protected $casts = [
        'year' => 'integer',
        'quota' => 'decimal:2',
        'used' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }
}

