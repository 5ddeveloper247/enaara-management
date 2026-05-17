<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_roster_entry_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_roster_entry_id')
                ->constrained('shift_roster_entries')
                ->cascadeOnDelete();
            $table->string('event', 20);
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('event_at');
            $table->string('summary', 500)->nullable();
            $table->json('changes')->nullable();
            $table->timestamps();

            $table->index(['shift_roster_entry_id', 'event_at'], 'shift_roster_entry_events_entry_at_index');
            $table->index('event');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_roster_entry_events');
    }
};
