<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_bank_details', function (Blueprint $table) {
            $table->boolean('is_salary_account')->default(false)->after('account_type');
        });

        $firstIds = DB::table('employee_bank_details')
            ->selectRaw('MIN(id) as id')
            ->groupBy('employee_id')
            ->pluck('id');
        if ($firstIds->isNotEmpty()) {
            DB::table('employee_bank_details')->whereIn('id', $firstIds)->update(['is_salary_account' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('employee_bank_details', function (Blueprint $table) {
            $table->dropColumn('is_salary_account');
        });
    }
};
