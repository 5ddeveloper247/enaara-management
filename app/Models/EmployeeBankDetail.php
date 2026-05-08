<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeBankDetail extends Model
{
    protected $table    = 'employee_bank_details';
    protected $fillable = [
        'employee_id',
        'account_category',
        'account_title',
        'account_no',
        'bank_name',
        'branch_name',
        'branch_code',
        'branch_address',
        'bank_branch',
        'iban',
        'account_type',
        'is_salary_account',
    ];

    protected $casts = [
        'is_salary_account' => 'boolean',
    ];

    public function employee() { return $this->belongsTo(Employee::class); }
}
