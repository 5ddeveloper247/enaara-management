<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_files', function (Blueprint $table) {
            if (!Schema::hasColumn('media_files', 'attachment_type')) {
                $table->string('attachment_type', 100)->nullable()->after('file_type');
            }
            if (!Schema::hasColumn('media_files', 'title')) {
                $table->string('title')->nullable()->after('attachment_type');
            }
            if (!Schema::hasColumn('media_files', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('media_files', function (Blueprint $table) {
            if (Schema::hasColumn('media_files', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('media_files', 'title')) {
                $table->dropColumn('title');
            }
            if (Schema::hasColumn('media_files', 'attachment_type')) {
                $table->dropColumn('attachment_type');
            }
        });
    }
};
