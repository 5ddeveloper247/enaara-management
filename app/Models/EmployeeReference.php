<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeReference extends Model
{
    protected $table    = 'employee_references';
    protected $fillable = ['employee_id', 'ref_number', 'name', 'designation', 'organization', 'contact_no', 'relationship'];

    public function employee() { return $this->belongsTo(Employee::class); }
}
