<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeIdSequence extends Model
{
    protected $table    = 'entity_code_sequences';
    protected $fillable = ['sbu_id', 'prefix', 'last_number'];
}
