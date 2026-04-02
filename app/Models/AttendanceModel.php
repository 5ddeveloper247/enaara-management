<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\LogsActivity;

class AttendanceModel extends Model
{
    use LogsActivity;
    protected $table = 'attendance_models';

    protected $fillable = [
        'organization_id',
        'department_id',
        'name',
        'grace_minutes',
        'policy_json',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
