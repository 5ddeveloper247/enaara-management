<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceModelSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $orgIds = DB::table('organizations')->pluck('id');
        $modes = [
            ['name' => 'Biometric', 'grace_minutes' => 15, 'policy_json' => json_encode(['device_required' => true])],
            ['name' => 'Mobile App', 'grace_minutes' => 10, 'policy_json' => json_encode(['gps_required' => true])],
            ['name' => 'Web', 'grace_minutes' => 10, 'policy_json' => null],
            ['name' => 'Manual (Override)', 'grace_minutes' => 0, 'policy_json' => json_encode(['requires_approval' => true])],
        ];

        foreach ($orgIds as $orgId) {
            $departmentIds = DB::table('departments')->where('organization_id', $orgId)->pluck('id')->toArray();
            foreach ($modes as $mode) {
                $departmentId = !empty($departmentIds) ? ($departmentIds[array_rand($departmentIds)] ?? null) : null;
                DB::table('attendance_models')->insert([
                    'organization_id' => $orgId,
                    'department_id' => $departmentId,
                    'name' => $mode['name'],
                    'grace_minutes' => $mode['grace_minutes'],
                    'policy_json' => $mode['policy_json'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
