<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequiredDocumentType extends Model
{
    protected $fillable = ['name', 'status'];

    protected $casts = [
        'status' => 'boolean',
    ];
}
