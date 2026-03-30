<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePoliceVerification extends Model
{
    protected $table    = 'employee_police_verifications';
    protected $fillable = [
        'employee_id', 'verification_status', 'msr_letter_no', 'addressee',
        'verifying_authority', 'verification_letter_no', 'next_verification_date', 'remarks',
    ];
    protected $casts = ['next_verification_date' => 'date'];

    public function employee() { return $this->belongsTo(Employee::class); }
}
