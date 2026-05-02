<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeMedical extends Model
{
    protected $table    = 'employee_medicals';
    protected $fillable = [
        'employee_id',
        'last_fitness_test',
        'last_fitness_test_date',
        'last_fitness_test_result',
        'has_disability',
        'blood_group',
        'disability_type',
        'disability_description',
        'has_chronic_disease',
        'chronic_disease_description',
    ];

    protected $casts = [
        'last_fitness_test_date' => 'date',
    ];

    public function employee() { return $this->belongsTo(Employee::class); }
}
