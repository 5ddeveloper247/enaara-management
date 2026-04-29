<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeCertificate extends Model
{
    protected $fillable = [
        'employee_id',
        'certificate_name',
        'start_date',
        'end_date',
        'institute',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
}
