<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_medicals', function (Blueprint $table) {
            $table->string('has_chronic_disease', 5)->nullable()->after('disability_description');
            $table->text('chronic_disease_description')->nullable()->after('has_chronic_disease');
        });

        $rows = DB::table('employee_medicals')->where('disability_type', 'Chronic Disease')->get();
        foreach ($rows as $row) {
            DB::table('employee_medicals')->where('id', $row->id)->update([
                'has_chronic_disease' => 'yes',
                'chronic_disease_description' => $row->disability_description,
                'disability_type' => null,
                'disability_description' => null,
            ]);
        }
    }

    public function down(): void
    {
        $rows = DB::table('employee_medicals')->where('has_chronic_disease', 'yes')->get();
        foreach ($rows as $row) {
            if ($row->chronic_disease_description !== null && $row->chronic_disease_description !== '') {
                DB::table('employee_medicals')->where('id', $row->id)->update([
                    'disability_type' => 'Chronic Disease',
                    'disability_description' => $row->chronic_disease_description,
                ]);
            }
        }

        Schema::table('employee_medicals', function (Blueprint $table) {
            $table->dropColumn(['has_chronic_disease', 'chronic_disease_description']);
        });
    }
};
