<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('media_files', function (Blueprint $table) {
            if (!Schema::hasColumn('media_files', 'subsection')) {
                $table->string('subsection')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('media_files', function (Blueprint $table) {
            if (Schema::hasColumn('media_files', 'subsection')) {
                $table->dropColumn('subsection');
            }
        });
    }
};
