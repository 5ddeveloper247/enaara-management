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
            $table->string('account_category', 40)->nullable()->after('employee_id');
            $table->string('bank_name', 255)->nullable()->after('account_no');
            $table->string('branch_code', 100)->nullable()->after('bank_name');
            $table->string('branch_address', 500)->nullable()->after('branch_code');
            $table->string('iban', 34)->nullable()->after('branch_address');
        });

        if (Schema::hasColumn('employee_bank_details', 'bank_branch')) {
            DB::table('employee_bank_details')
                ->whereNotNull('bank_branch')
                ->whereNull('branch_address')
                ->update(['branch_address' => DB::raw('bank_branch')]);
        }
    }

    public function down(): void
    {
        Schema::table('employee_bank_details', function (Blueprint $table) {
            $table->dropColumn([
                'account_category',
                'bank_name',
                'branch_code',
                'branch_address',
                'iban',
            ]);
        });
    }
};
