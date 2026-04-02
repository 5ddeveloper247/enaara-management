<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeMedical extends Model
{
    protected $table    = 'employee_medicals';
    protected $fillable = ['employee_id', 'last_fitness_test', 'has_disability', 'blood_group', 'disability_type', 'disability_description'];

    public function employee() { return $this->belongsTo(Employee::class); }
}
