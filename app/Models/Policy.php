<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\LogsActivity;

class Policy extends Model
{
    use LogsActivity;
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'status',
        'effective_date',
        'applicable_to',
        'applicable_details',
        'description',
        'document_path',
        'document_name',
    ];

    protected $casts = [
        'effective_date' => 'date',
    ];
}
