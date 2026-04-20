<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateNames = DB::table('module_categories')
            ->selectRaw('LOWER(TRIM(category_name)) as normalized_name, COUNT(*) as total')
            ->groupBy('normalized_name')
            ->having('total', '>', 1)
            ->pluck('normalized_name');

        if ($duplicateNames->isNotEmpty()) {
            throw new RuntimeException('Cannot add unique index to module_categories.category_name because duplicate names already exist.');
        }

        Schema::table('module_categories', function (Blueprint $table) {
            $table->unique('category_name', 'module_categories_category_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('module_categories', function (Blueprint $table) {
            $table->dropUnique('module_categories_category_name_unique');
        });
    }
};
