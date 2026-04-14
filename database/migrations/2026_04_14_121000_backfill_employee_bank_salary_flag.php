<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('employee_bank_details', 'is_salary_account')) {
            return;
        }
        $employeeIds = DB::table('employee_bank_details')->distinct()->pluck('employee_id');
        foreach ($employeeIds as $eid) {
            $has = DB::table('employee_bank_details')
                ->where('employee_id', $eid)
                ->where('is_salary_account', true)
                ->exists();
            if ($has) {
                continue;
            }
            $firstId = DB::table('employee_bank_details')
                ->where('employee_id', $eid)
                ->orderBy('id')
                ->value('id');
            if ($firstId) {
                DB::table('employee_bank_details')->where('id', $firstId)->update(['is_salary_account' => true]);
            }
        }
    }

    public function down(): void
    {
    }
};
