<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Traits\LogsActivity;

class LeaveBalanceAdjustment extends Model
{
    use LogsActivity;
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'organization_id',
        'department_id',
        'leave_type_id',
        'adjustment_type',
        'days',
        'reason',
        'adjusted_by',
        'leave_quota_id',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function adjustedBy()
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    public function leaveQuota()
    {
        return $this->belongsTo(EmployeeLeaveQuota::class, 'leave_quota_id');
    }
}
