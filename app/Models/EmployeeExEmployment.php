<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeExEmployment extends Model
{
    protected $table    = 'employee_ex_employments';
    protected $fillable = ['employee_id', 'organization', 'designation', 'from_date', 'to_date', 'salary', 'reason_for_leaving', 'hr_contact', 'hr_email'];
    protected $casts    = ['from_date' => 'date', 'to_date' => 'date'];

    public function employee() { return $this->belongsTo(Employee::class); }
}
