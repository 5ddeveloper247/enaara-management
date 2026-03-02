<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SbuFloorSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $sbuIds = DB::table('sbus')->pluck('id');
        $floorTemplates = [
            ['name' => 'Ground Floor', 'floor_number' => 0, 'floor_type' => 'operational', 'is_restricted' => false],
            ['name' => '1st Floor', 'floor_number' => 1, 'floor_type' => 'operational', 'is_restricted' => false],
            ['name' => '2nd Floor', 'floor_number' => 2, 'floor_type' => 'operational', 'is_restricted' => false],
            ['name' => '3rd Floor', 'floor_number' => 3, 'floor_type' => 'mixed', 'is_restricted' => false],
            ['name' => '4th Floor', 'floor_number' => 4, 'floor_type' => 'corporate', 'is_restricted' => true],
        ];

        foreach ($sbuIds as $sbuId) {
            foreach ($floorTemplates as $floor) {
                DB::table('sbu_floors')->insert([
                    'sbu_id' => $sbuId,
                    'name' => $floor['name'],
                    'floor_number' => $floor['floor_number'],
                    'floor_type' => $floor['floor_type'],
                    'is_restricted' => $floor['is_restricted'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
