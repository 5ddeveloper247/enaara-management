<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkModelSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $orgIds = DB::table('organizations')->pluck('id');
        $models = [
            ['name' => 'Ordinary (On-site)', 'code' => 'ORD', 'default_schedule_json' => json_encode(['days_onsite' => 5])],
            ['name' => 'Hybrid', 'code' => 'HYBR', 'default_schedule_json' => json_encode(['days_onsite' => 3, 'days_wfh' => 2])],
            ['name' => 'Work From Home (WFH)', 'code' => 'WFH', 'default_schedule_json' => json_encode(['days_onsite' => 0])],
            ['name' => 'Floating (SBU to SBU)', 'code' => 'FLOAT', 'default_schedule_json' => null],
        ];

        foreach ($orgIds as $orgId) {
            $departmentIds = DB::table('departments')->where('organization_id', $orgId)->pluck('id')->toArray();
            foreach ($models as $model) {
                $departmentId = !empty($departmentIds) ? ($departmentIds[array_rand($departmentIds)] ?? null) : null;
                DB::table('work_models')->insert([
                    'organization_id' => $orgId,
                    'department_id' => $departmentId,
                    'name' => $model['name'],
                    'code' => $model['code'],
                    'default_schedule_json' => $model['default_schedule_json'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
