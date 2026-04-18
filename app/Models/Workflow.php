<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workflow extends Model
{
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'request_type',
        'status',
        'organization_id',
        'sbu_id',
        'branch',
        'approval_levels',
        'sla_hours',
        'escalate_to',
    ];

    protected $casts = [
        'approval_levels' => 'array',
        'sla_hours' => 'integer',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function sbu(): BelongsTo
    {
        return $this->belongsTo(Sbu::class, 'sbu_id');
    }
}
