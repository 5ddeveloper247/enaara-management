<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\LogsActivity;

class Organization extends Model
{
    use LogsActivity;

    protected $fillable = [
        'parent_id',
        'name',
        'code',
        'email',
        'tax_no',
        'description',
        'address',
        'working_days',
        'working_start_time',
        'working_end_time',
        'opening_grace_period',
        'closing_grace_period',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'working_days' => 'array',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Organization::class, 'parent_id');
    }

    public function employeeTypes(): HasMany
    {
        return $this->hasMany(EmployeeType::class, 'organization_id');
    }

    public function workModels(): HasMany
    {
        return $this->hasMany(WorkModel::class, 'organization_id');
    }

    public function attendanceModels(): HasMany
    {
        return $this->hasMany(AttendanceModel::class, 'organization_id');
    }

    public function shiftTypes(): HasMany
    {
        return $this->hasMany(ShiftType::class, 'organization_id');
    }

    public function sbus(): HasMany
    {
        return $this->hasMany(Sbu::class, 'organization_id');
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class, 'organization_id');
    }

    public function leaveTypes(): HasMany
    {
        return $this->hasMany(LeaveType::class, 'organization_id');
    }

    public function publicHolidays()
    {
        return $this->belongsToMany(PublicHoliday::class, 'holiday_organization', 'organization_id', 'public_holiday_id');
    }
}
