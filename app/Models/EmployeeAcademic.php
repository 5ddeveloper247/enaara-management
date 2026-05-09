<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeAcademic extends Model
{
    protected $table    = 'employee_academics';
    protected $fillable = ['employee_id', 'degree', 'degree_title', 'grade_cgpa', 'start_date', 'end_date', 'field_of_study', 'institute'];
    protected $casts    = ['start_date' => 'date', 'end_date' => 'date'];

    public function employee() { return $this->belongsTo(Employee::class); }
}
