<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeContact extends Model
{
    protected $table    = 'employee_contacts';
    protected $fillable = [
        'employee_id', 'residence_phone', 'emergency_contact',
        'cell_no', 'email', 'present_address', 'permanent_address',
    ];

    public function employee() { return $this->belongsTo(Employee::class); }
}
