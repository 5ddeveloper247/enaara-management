<?php

namespace App\Http\Requests\Admin\Employee\Concerns;

trait NormalizesBankRowsFromRequest
{
    protected function normalizeBankRowsFromRequest(): void
    {
        $banks = $this->input('banks', []);
        if (! is_array($banks)) {
            $banks = [];
        }
        $normalized = [];
        foreach ($banks as $row) {
            if (! is_array($row)) {
                continue;
            }
            $accountTitle = trim((string) ($row['account_title'] ?? ''));
            $accountNo = preg_replace('/\s+/', '', (string) ($row['account_no'] ?? ''));
            $iban = strtoupper(preg_replace('/\s+/', '', (string) ($row['iban'] ?? '')));
            $row['account_title'] = $accountTitle;
            $row['account_no'] = $accountNo;
            $row['iban'] = $iban;
            $row['bank_name'] = trim((string) ($row['bank_name'] ?? ''));
            $row['branch_code'] = trim((string) ($row['branch_code'] ?? ''));
            $row['branch_address'] = trim((string) ($row['branch_address'] ?? ''));
            $row['account_category'] = isset($row['account_category']) ? trim((string) $row['account_category']) : null;
            $row['account_type'] = isset($row['account_type']) ? trim((string) $row['account_type']) : null;
            $row['is_salary_account'] = filter_var($row['is_salary_account'] ?? false, FILTER_VALIDATE_BOOLEAN);
            if ($accountTitle === '' && $accountNo === '') {
                continue;
            }
            $normalized[] = $row;
        }
        $this->merge(['banks' => array_values($normalized)]);
    }
}
