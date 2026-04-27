<?php

namespace App\Http\Requests\Admin\Employee\Concerns;

use Illuminate\Support\Facades\DB;

trait ValidatesUniqueBankIdentifiers
{
    protected function assertUniqueBankIdentifiers($validator): void
    {
        $employeeId = $this->resolveEmployeeIdForBankUniqueness();
        $rows = [];

        $banks = $this->input('banks');
        if (is_array($banks)) {
            foreach ($banks as $index => $row) {
                if (!is_array($row)) {
                    continue;
                }
                $accountNo = preg_replace('/\s+/', '', (string) ($row['account_no'] ?? ''));
                $iban = strtoupper(preg_replace('/\s+/', '', (string) ($row['iban'] ?? '')));
                $rows[] = [
                    'account_no' => $accountNo,
                    'iban' => $iban,
                    'bank_detail_id' => (int) ($row['bank_detail_id'] ?? $row['id'] ?? 0),
                    'account_attr' => "banks.{$index}.account_no",
                    'iban_attr' => "banks.{$index}.iban",
                ];
            }
        } else {
            $accountNo = preg_replace('/\s+/', '', (string) $this->input('account_no', ''));
            $iban = strtoupper(preg_replace('/\s+/', '', (string) $this->input('iban', '')));
            if ($accountNo !== '' || $iban !== '') {
                $rows[] = [
                    'account_no' => $accountNo,
                    'iban' => $iban,
                    'bank_detail_id' => (int) $this->input('bank_detail_id', 0),
                    'account_attr' => 'account_no',
                    'iban_attr' => 'iban',
                ];
            }
        }

        if (empty($rows)) {
            return;
        }

        $seenAccount = [];
        $seenIban = [];
        foreach ($rows as $row) {
            $accountNo = $row['account_no'];
            $iban = $row['iban'];

            if ($accountNo !== '') {
                if (isset($seenAccount[$accountNo])) {
                    $validator->errors()->add($row['account_attr'], 'Account number already used.');
                } else {
                    $seenAccount[$accountNo] = true;
                }
            }

            if ($iban !== '') {
                if (isset($seenIban[$iban])) {
                    $validator->errors()->add($row['iban_attr'], 'IBAN already used.');
                } else {
                    $seenIban[$iban] = true;
                }
            }
        }

        foreach ($rows as $row) {
            if ($row['account_no'] !== '') {
                if ($employeeId > 0) {
                    $sameEmployeeQuery = DB::table('employee_bank_details')
                        ->where('employee_id', $employeeId)
                        ->where('account_no', $row['account_no']);
                    if ($row['bank_detail_id'] > 0) {
                        $sameEmployeeQuery->where('id', '!=', $row['bank_detail_id']);
                    }
                    if ($sameEmployeeQuery->exists()) {
                        $validator->errors()->add($row['account_attr'], 'Account number already used.');
                    }
                }

                $otherEmployeeQuery = DB::table('employee_bank_details')->where('account_no', $row['account_no']);
                if ($employeeId > 0) {
                    $otherEmployeeQuery->where('employee_id', '!=', $employeeId);
                }
                if ($otherEmployeeQuery->exists()) {
                    $validator->errors()->add($row['account_attr'], 'Account number already exists.');
                }
            }

            if ($row['iban'] !== '') {
                if ($employeeId > 0) {
                    $sameEmployeeQuery = DB::table('employee_bank_details')
                        ->where('employee_id', $employeeId)
                        ->where('iban', $row['iban']);
                    if ($row['bank_detail_id'] > 0) {
                        $sameEmployeeQuery->where('id', '!=', $row['bank_detail_id']);
                    }
                    if ($sameEmployeeQuery->exists()) {
                        $validator->errors()->add($row['iban_attr'], 'IBAN already used.');
                    }
                }

                $otherEmployeeQuery = DB::table('employee_bank_details')->where('iban', $row['iban']);
                if ($employeeId > 0) {
                    $otherEmployeeQuery->where('employee_id', '!=', $employeeId);
                }
                if ($otherEmployeeQuery->exists()) {
                    $validator->errors()->add($row['iban_attr'], 'IBAN already exists.');
                }
            }
        }
    }

    protected function resolveEmployeeIdForBankUniqueness(): int
    {
        $candidate = $this->input('employee_id');
        if ($candidate === null || $candidate === '') {
            $candidate = $this->route('employee');
        }
        if (is_object($candidate) && isset($candidate->id)) {
            $candidate = $candidate->id;
        }
        return (int) $candidate;
    }
}
