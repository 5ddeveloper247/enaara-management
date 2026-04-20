<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateModuleNames = DB::table('modules')
            ->whereNull('deleted_at')
            ->whereNotNull('module_name')
            ->select('module_name')
            ->groupBy('module_name')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('module_name');

        if ($duplicateModuleNames->isNotEmpty()) {
            throw new RuntimeException('Cannot add unique index to modules.module_name because duplicate values already exist.');
        }

        $duplicateDisplayOrders = DB::table('modules')
            ->whereNull('deleted_at')
            ->whereNotNull('display_order')
            ->select('display_order')
            ->groupBy('display_order')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('display_order');

        if ($duplicateDisplayOrders->isNotEmpty()) {
            foreach ($duplicateDisplayOrders as $displayOrder) {
                $ids = DB::table('modules')
                    ->whereNull('deleted_at')
                    ->where('display_order', $displayOrder)
                    ->orderBy('id')
                    ->pluck('id')
                    ->values();

                if ($ids->count() > 1) {
                    DB::table('modules')
                        ->whereIn('id', $ids->slice(1)->all())
                        ->update(['display_order' => null]);
                }
            }
        }

        Schema::table('modules', function (Blueprint $table) {
            $table->unique('module_name', 'modules_module_name_unique');
            $table->unique('display_order', 'modules_display_order_unique');
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropUnique('modules_module_name_unique');
            $table->dropUnique('modules_display_order_unique');
        });
    }
};
