<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeArmedForce extends Model
{
    protected $table    = 'employee_armed_forces';
    protected $fillable = [
        'employee_id', 'service_no', 'rank', 'medical_category',
        'date_of_commissioning', 'date_of_retirement', 'reason_of_retirement',
        'corps_regiment', 'ex_army_unit', 'trade', 'pma_lc_ots',
    ];
    protected $casts = ['date_of_commissioning' => 'date', 'date_of_retirement' => 'date'];

    public function employee() { return $this->belongsTo(Employee::class); }
}
