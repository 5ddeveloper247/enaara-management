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
        Schema::create('module_categories', function (Blueprint $table) {
            $table->increments('ID'); // int unsigned auto increment

            $table->string('category_name', 155)->nullable();
            $table->string('css_class', 100)->nullable();
            $table->unsignedInteger('display_order')->nullable();

            $table->timestamps();     // created_at & updated_at
            $table->softDeletes();    // deleted_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_categories');
    }
};
