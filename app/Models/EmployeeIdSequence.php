<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeIdSequence extends Model
{
    protected $table    = 'employee_id_sequences';
    protected $fillable = ['prefix', 'last_number'];
}
