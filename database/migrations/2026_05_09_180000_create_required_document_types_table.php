<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('required_document_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        // Seed initial data
        $initialTypes = [
            'CNIC / National ID',
            'Family registration certificate',
            'Academic certificate',
            'Professional certificates',
            'Previous experience letter',
            'Medical fitness certificate',
            'Police verification letter'
        ];

        foreach ($initialTypes as $type) {
            DB::table('required_document_types')->insert([
                'name' => $type,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('required_document_types');
    }
};
