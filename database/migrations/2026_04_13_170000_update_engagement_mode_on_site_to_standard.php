<?php

use App\Models\Employee;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Employee::query()->where('engagement_mode', 'on_site')->update(['engagement_mode' => 'standard']);
    }

    public function down(): void
    {
        Employee::query()->where('engagement_mode', 'standard')->update(['engagement_mode' => 'on_site']);
    }
};
