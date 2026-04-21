<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeFamilyMember extends Model
{
    protected $table    = 'employee_family_members';
    protected $fillable = [
        'employee_id',
        'name',
        'gender',
        'dob',
        'relation',
        'occupation',
        'is_next_of_kin',
        'nok_cnic',
        'nok_cnic_expiry_date',
        'nok_contact',
    ];

    protected $casts = [
        'dob'                 => 'date',
        'nok_cnic_expiry_date' => 'date',
        'is_next_of_kin'     => 'boolean',
    ];

    public function employee() { return $this->belongsTo(Employee::class); }
}
