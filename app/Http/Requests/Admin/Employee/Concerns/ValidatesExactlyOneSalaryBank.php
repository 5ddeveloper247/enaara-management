<?php

namespace App\Http\Requests\Admin\Employee\Concerns;

use Illuminate\Validation\Validator;

trait ValidatesExactlyOneSalaryBank
{
    protected function assertAtLeastOneSalaryBank(Validator $validator): void
    {
        $banks = $this->input('banks', []);
        if (! is_array($banks) || $banks === []) {
            return;
        }
        $n = 0;
        foreach ($banks as $row) {
            if (! is_array($row)) {
                continue;
            }
            $flag = $row['is_salary_account'] ?? false;
            if ($flag === true || $flag === 1 || $flag === '1') {
                $n++;
            }
        }
        if ($n < 1) {
            $validator->errors()->add('banks', 'Select at least one account for salary (payroll) using "Use for salary (payroll)".');
        }
    }
}
