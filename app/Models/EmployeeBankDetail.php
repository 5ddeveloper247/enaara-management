<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeBankDetail extends Model
{
    protected $table    = 'employee_bank_details';
    protected $fillable = ['employee_id', 'account_title', 'account_no', 'bank_branch', 'account_type'];

    public function employee() { return $this->belongsTo(Employee::class); }
}
