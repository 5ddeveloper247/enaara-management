<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePoliceVerification extends Model
{
    protected $table    = 'employee_police_verifications';
    protected $fillable = [
        'employee_id', 'verification_status', 'msr_letter_no', 'msr_date', 'addressee',
        'verifying_authority', 'verification_letter_no', 'verification_letter_date', 'next_verification_date', 'remarks',
    ];
    protected $casts = [
        'next_verification_date' => 'date',
        'msr_date'               => 'date',
        'verification_letter_date' => 'date',
    ];

    public function employee() { return $this->belongsTo(Employee::class); }
}
