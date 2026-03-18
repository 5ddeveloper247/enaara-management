<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    protected $table = 'employees';

    protected $fillable = [
        'name',
        'organization_id',
        'department_id',
        'employee_type_id',
        'email',
        'phone_number',
        'cnic',
        'gender',
        'nationality',
        'dob',
        'line_manager_id',
        'is_manager',
        'is_active',
        'role_id',
    ];

    protected $casts = [
        'is_manager' => 'boolean',
        'is_active' => 'boolean',
        'dob' => 'date',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function lineManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'line_manager_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'employee_id');
    }
}
