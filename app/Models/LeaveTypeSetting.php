<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveTypeSetting extends Model
{
    protected $table = 'leave_type_settings';

    protected $fillable = [
        'leave_type_id',
        'employment_type',
        'gender',
        'min_service_months',
        'eligible_from',
        'probation_eligible',
        'unit_of_leave',
        'accrual_frequency',
        'accrual_start_month',
        'carry_forward',
        'max_carry_forward_days',
        'encashment_allowed',
        'encashment_rule',
        'max_consecutive_days',
        'advance_notice_days',
        'short_leave_applicable',
        'short_leave_max_hours',
        'half_day_applicable',
    ];

    protected $casts = [
        'min_service_months' => 'integer',
        'probation_eligible' => 'boolean',
        'accrual_start_month' => 'integer',
        'max_carry_forward_days' => 'decimal:2',
        'max_consecutive_days' => 'integer',
        'advance_notice_days' => 'integer',
        'short_leave_applicable' => 'boolean',
        'short_leave_max_hours' => 'integer',
        'half_day_applicable' => 'boolean',
    ];

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }
}
