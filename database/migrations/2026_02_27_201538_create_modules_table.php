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
        Schema::create('modules', function (Blueprint $table) {

            $table->id();

            $table->unsignedInteger('module_category_id')->nullable();

            $table->string('module_name', 155)->nullable();
            $table->string('route', 155)->nullable();

            $table->tinyInteger('show_in_menu')
                  ->default(1)
                  ->comment('0=No, 1=Yes');

            $table->string('css_class', 100)->nullable();
            $table->unsignedInteger('display_order')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign Key
            $table->foreign('module_category_id')
                  ->references('id')
                  ->on('module_categories')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
