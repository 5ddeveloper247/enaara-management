<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Policy extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'title',
        'category',
        'status',
        'effective_date',
        'applicable_to',
        'applicable_details',
        'organization_id',
        'sbu_id',
        'sbu_floor_id',
        'description',
        'document_path',
        'document_name',
    ];

    protected $casts = [
        'effective_date' => 'date',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function sbu(): BelongsTo
    {
        return $this->belongsTo(Sbu::class, 'sbu_id');
    }

    public function sbuFloor(): BelongsTo
    {
        return $this->belongsTo(SbuFloor::class, 'sbu_floor_id');
    }
}
