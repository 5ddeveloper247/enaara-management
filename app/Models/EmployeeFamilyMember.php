<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeFamilyMember extends Model
{
    protected $table    = 'employee_family_members';
    protected $fillable = ['employee_id', 'name', 'gender', 'dob', 'relation', 'occupation'];
    protected $casts    = ['dob' => 'date'];

    public function employee() { return $this->belongsTo(Employee::class); }
}
