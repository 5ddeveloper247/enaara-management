<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SbuSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('sbus')->insert([
            [
                'organization_id' => 1,
                'name' => 'Madison Square Mall – Rawalpindi',
                'city' => 'Rawalpindi',
                'address' => '6th Road, Murree Road, Rawalpindi, Punjab',
                'latitude' => 33.597716,
                'longitude' => 73.076653,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'organization_id' => 1,
                'name' => 'Madison Square Mall – Lahore',
                'city' => 'Lahore',
                'address' => 'Main Boulevard, Gulberg III, Lahore',
                'latitude' => 31.520370,
                'longitude' => 74.358749,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'organization_id' => 1,
                'name' => 'Royal Swiss – Lahore',
                'city' => 'Lahore',
                'address' => 'Lahore',
                'latitude' => 31.520370,
                'longitude' => 74.358749,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
