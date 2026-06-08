<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('leave_balance_adjustments')) {
            return;
        }

        Schema::table('leave_balance_adjustments', function (Blueprint $table) {
            if (! Schema::hasColumn('leave_balance_adjustments', 'previous_remaining')) {
                $table->decimal('previous_remaining', 5, 2)->nullable()->after('days');
            }
            if (! Schema::hasColumn('leave_balance_adjustments', 'new_remaining')) {
                $table->decimal('new_remaining', 5, 2)->nullable()->after('previous_remaining');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('leave_balance_adjustments')) {
            return;
        }

        Schema::table('leave_balance_adjustments', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('leave_balance_adjustments', 'new_remaining') ? 'new_remaining' : null,
                Schema::hasColumn('leave_balance_adjustments', 'previous_remaining') ? 'previous_remaining' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
