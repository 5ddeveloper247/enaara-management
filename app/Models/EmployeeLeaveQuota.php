<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\LeaveBalanceAdjustment;

use App\Traits\LogsActivity;

class EmployeeLeaveQuota extends Model
{
    use LogsActivity;
    protected $table = 'employee_leave_quotas';

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'department_id',
        'year',
        'quota',
        'used',
        'carried_forward',
        'encashed',
    ];

    protected $casts = [
        'year' => 'integer',
        'quota' => 'decimal:2',
        'used' => 'decimal:2',
        'carried_forward' => 'decimal:2',
        'encashed' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Get the quota after manual adjustments (Add/Subtract).
     */
    public function getAdjustedQuotaAttribute(): float
    {
        $adjustments = LeaveBalanceAdjustment::where('employee_id', $this->employee_id)
            ->where('leave_type_id', $this->leave_type_id)
            ->whereYear('created_at', $this->year)
            ->selectRaw("SUM(CASE WHEN adjustment_type = 'add' THEN days ELSE -days END) as total")
            ->value('total') ?? 0;

        return (float) $this->quota + (float) $adjustments;
    }

    /**
     * Calculate the remaining balance based on adjusted quota and used leaves.
     */
    public function getRemainingBalanceAttribute(): float
    {
        return $this->adjusted_quota - (float) $this->used;
    }

    public function leaveBalanceAdjustments()
    {
        return $this->hasMany(LeaveBalanceAdjustment::class, 'leave_quota_id');
    }
}

