<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE employee_academics MODIFY grade_cgpa VARCHAR(100) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE employee_academics MODIFY grade_cgpa VARCHAR(50) NULL');
    }
};
