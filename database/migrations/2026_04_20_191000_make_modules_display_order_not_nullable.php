<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $maxDisplayOrder = (int) (DB::table('modules')
            ->whereNotNull('display_order')
            ->max('display_order') ?? 0);

        $nullDisplayOrderIds = DB::table('modules')
            ->whereNull('display_order')
            ->orderBy('id')
            ->pluck('id');

        foreach ($nullDisplayOrderIds as $id) {
            $maxDisplayOrder++;
            DB::table('modules')
                ->where('id', $id)
                ->update(['display_order' => $maxDisplayOrder]);
        }

        Schema::table('modules', function (Blueprint $table) {
            $table->unsignedInteger('display_order')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->unsignedInteger('display_order')->nullable()->change();
        });
    }
};
