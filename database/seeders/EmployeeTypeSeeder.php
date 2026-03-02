<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $orgIds = DB::table('organizations')->pluck('id');
        $types = [
            ['name' => 'Permanent', 'code' => 'PERM', 'rules_json' => json_encode(['probation_months' => 3])],
            ['name' => 'Contract', 'code' => 'CONT', 'rules_json' => json_encode(['max_duration_months' => 12])],
            ['name' => 'Part-Time', 'code' => 'PART', 'rules_json' => json_encode(['max_hours_per_week' => 25])],
            ['name' => 'Consultant', 'code' => 'CONS', 'rules_json' => null],
            ['name' => 'Vendor / Third Party', 'code' => 'VEND', 'rules_json' => null],
        ];

        foreach ($orgIds as $orgId) {
            foreach ($types as $type) {
                DB::table('employee_types')->insert([
                    'organization_id' => $orgId,
                    'name' => $type['name'],
                    'code' => $type['code'],
                    'rules_json' => $type['rules_json'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
