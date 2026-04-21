<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_family_members', function (Blueprint $table) {
            $table->boolean('is_next_of_kin')->default(false)->after('occupation');
            $table->string('nok_cnic', 20)->nullable()->after('is_next_of_kin');
            $table->date('nok_cnic_expiry_date')->nullable()->after('nok_cnic');
            $table->string('nok_contact', 30)->nullable()->after('nok_cnic_expiry_date');
        });

        $employees = DB::table('employees')
            ->whereNotNull('nok_name')
            ->where('nok_name', '!=', '')
            ->select(
                'id',
                'nok_name',
                'nok_cnic',
                'nok_cnic_expiry_date',
                'nok_contact'
            )
            ->get();

        foreach ($employees as $emp) {
            $name = trim((string) $emp->nok_name);
            $memberId = DB::table('employee_family_members')
                ->where('employee_id', $emp->id)
                ->whereRaw('TRIM(name) = ?', [$name])
                ->orderByDesc('id')
                ->value('id');
            if ($memberId) {
                DB::table('employee_family_members')->where('id', $memberId)->update([
                    'is_next_of_kin'         => true,
                    'nok_cnic'               => $emp->nok_cnic,
                    'nok_cnic_expiry_date'   => $emp->nok_cnic_expiry_date,
                    'nok_contact'            => $emp->nok_contact,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('employee_family_members', function (Blueprint $table) {
            $table->dropColumn(['is_next_of_kin', 'nok_cnic', 'nok_cnic_expiry_date', 'nok_contact']);
        });
    }
};
