<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $orgIds = DB::table('organizations')->pluck('id');
        $shifts = [
            ['name' => 'Morning', 'code' => 'MORN', 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'break_duration_minutes' => 60, 'is_night_shift' => false],
            ['name' => 'Evening', 'code' => 'EVE', 'start_time' => '14:00:00', 'end_time' => '22:00:00', 'break_duration_minutes' => 60, 'is_night_shift' => false],
            ['name' => 'Night', 'code' => 'NIGHT', 'start_time' => '22:00:00', 'end_time' => '06:00:00', 'break_duration_minutes' => 60, 'is_night_shift' => true],
            ['name' => 'General', 'code' => 'GEN', 'start_time' => '08:00:00', 'end_time' => '16:00:00', 'break_duration_minutes' => 45, 'is_night_shift' => false],
        ];

        foreach ($orgIds as $orgId) {
            $departmentIds = DB::table('departments')->where('organization_id', $orgId)->pluck('id')->toArray();
            foreach ($shifts as $shift) {
                $departmentId = !empty($departmentIds) ? ($departmentIds[array_rand($departmentIds)] ?? null) : null;
                DB::table('shift_types')->insert([
                    'organization_id' => $orgId,
                    'department_id' => $departmentId,
                    'name' => $shift['name'],
                    'code' => $shift['code'],
                    'start_time' => $shift['start_time'],
                    'end_time' => $shift['end_time'],
                    'break_duration_minutes' => $shift['break_duration_minutes'],
                    'is_night_shift' => $shift['is_night_shift'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
