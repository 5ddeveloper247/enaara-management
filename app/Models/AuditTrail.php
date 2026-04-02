<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditTrail extends Model
{
    protected $fillable = [
        'action_at',
        'user_id',
        'employee_id',
        'organization_id',
        'sbu_id',
        'department_id',
        'module',
        'action',
        'action_category',
        'severity',
        'description',
        'ip_address',
        'user_agent',
        'device',
        'auditable_type',
        'auditable_id',
        'meta',
        'context',
    ];

    protected $casts = [
        'action_at' => 'datetime',
        'meta' => 'array',
        'context' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function changes(): HasMany
    {
        return $this->hasMany(AuditTrailChange::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
}