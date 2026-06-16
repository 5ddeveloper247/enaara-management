<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeWorkAssignment extends Model
{
    public const TYPE_WORK_FROM_HOME = 'work_from_home';

    public const TYPE_OUTSTATION = 'outstation';

    public const TYPE_ABSENT = 'absent';

    protected $fillable = [
        'employee_id',
        'assignment_date',
        'work_type',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'assignment_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
