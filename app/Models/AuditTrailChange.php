<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditTrailChange extends Model
{
    protected $fillable = [
        'audit_trail_id',
        'field',
        'old_value',
        'new_value',
    ];

    public function auditTrail(): BelongsTo
    {
        return $this->belongsTo(AuditTrail::class);
    }
}