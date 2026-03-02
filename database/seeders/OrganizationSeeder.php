<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('organizations')->insert([
            [
                'id' => 1,
                'parent_id' => null,
                'name' => 'Enaara Developers',
                'code' => 'ENA',
                'email' => 'info@enaara.com',
                'tax_no' => 'NTN-1234567-8',
                'description' => 'Parent organization - Enaara Facilities Management',
                'address' => 'Head Office, Islamabad',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'parent_id' => 1,
                'name' => 'Madison Square Mall Rawalpindi',
                'code' => 'MSM-RWP',
                'email' => 'rwp@madisonsquaremall.com',
                'tax_no' => null,
                'description' => 'Operational - Madison Square Mall Rawalpindi',
                'address' => '6th Road, Murree Road, Rawalpindi',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'parent_id' => 1,
                'name' => 'Madison Square Mall Lahore',
                'code' => 'MSM-LHE',
                'email' => 'lahore@madisonsquaremall.com',
                'tax_no' => null,
                'description' => 'Under Construction - Madison Square Mall Lahore',
                'address' => 'Main Boulevard, Lahore',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'parent_id' => 1,
                'name' => 'Royal Swiss Lahore',
                'code' => 'RS-LHE',
                'email' => 'info@royalswiss.pk',
                'tax_no' => null,
                'description' => 'Royal Swiss Lahore operations',
                'address' => 'Lahore',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
