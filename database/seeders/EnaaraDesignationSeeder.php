<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EnaaraDesignationSeeder extends Seeder
{
    public function run(): void
    {
        $organizationName = 'Enaara Facilities Management Services Limited';
        $sbuName = 'MSR';

        $organization = DB::table('organizations')
            ->where('name', $organizationName)
            ->first();

        if (!$organization) {
            $this->command->error("Organization not found: {$organizationName}");
            return;
        }

        $sbu = DB::table('sbus')
            ->where('organization_id', $organization->id)
            ->where('name', $sbuName)
            ->first();

        if (!$sbu) {
            $this->command->error("SBU not found: {$sbuName}");
            return;
        }

        $records = [
            ['Accounts & Finance', 'Accounts & Finance Executive'],
            ['Accounts & Finance', 'AM Finance & Taxation'],
            ['Accounts & Finance', 'Cashier'],
            ['Accounts & Finance', 'GM Finance & Accounts'],
            ['Accounts & Finance', 'Manager Accounts & Finance'],

            ['Admin', 'Cradle Operator'],
            ['Admin', 'Gardner'],
            ['Admin', 'Office Boy'],
            ['Admin', 'Office Boy'],
            ['Admin', 'Senior Office Boy'],
            ['Admin', 'Store & Procurement Executive'],
            ['Admin', 'Store & Procurement Officer'],
            ['Admin', 'Store Assistant'],
            ['Admin', 'Store Labour'],
            ['Admin', 'Sweeper'],

            ['Architecture & Civil Works', 'Carpenter helper'],
            ['Architecture & Civil Works', 'Assistant Manager Civil Works'],
            ['Architecture & Civil Works', 'Auto CAD Operator'],
            ['Architecture & Civil Works', 'Carpenter'],
            ['Architecture & Civil Works', 'Drain Opener'],
            ['Architecture & Civil Works', 'Helper'],
            ['Architecture & Civil Works', 'Labour'],
            ['Architecture & Civil Works', 'LPG Helper'],
            ['Architecture & Civil Works', 'LPG Operator'],
            ['Architecture & Civil Works', 'LPG Supervisor'],
            ['Architecture & Civil Works', 'Painter'],
            ['Architecture & Civil Works', 'Plumber'],
            ['Architecture & Civil Works', 'Plumbing Helper'],
            ['Architecture & Civil Works', 'Welder'],

            ['Food Court', 'Food Court Supervisor'],
            ['Food Court', 'Waiter'],

            ['HSE', 'Fire Alaram Technician'],
            ['HSE', 'HSE Manager'],
            ['HSE', 'HSE Officer'],
            ['HSE', 'HSE Supervisor'],
            ['HSE', 'MTO-HSE'],

            ['Human Resource', 'HR Executive OD & HRIS'],
            ['Human Resource', 'HR Executive Recruitment & Induction'],
            ['Human Resource', 'HR Manager'],
            ['Human Resource', 'HR Operations Executive'],

            ['IT', 'CCTV Operator'],
            ['IT', 'IT Support'],
            ['IT', 'Senior Manager IT'],

            ['Marketing', 'AM Brand & Activation'],
            ['Marketing', 'Creative Head (MSR)'],
            ['Marketing', 'Media Production Manager'],
            ['Marketing', 'Senior Graphic Designer'],

            ['MEP-Electrical', 'BMS Technician'],
            ['MEP-Electrical', 'Electrical Manager'],
            ['MEP-Electrical', 'Electrical Supervisor'],
            ['MEP-Electrical', 'Electrician'],
            ['MEP-Electrical', 'Electrician'],
            ['MEP-Electrical', 'Electrician Helper'],
            ['MEP-Electrical', 'Handyman'],

            ['MEP-HVAC', 'HVAC Executive'],
            ['MEP-HVAC', 'HVAC Officer'],
            ['MEP-HVAC', 'MEP Consultant'],

            ['MEP-VTS', 'Assistant Manager VTS'],
            ['MEP-VTS', 'Assistant Technician'],
            ['MEP-VTS', 'VTS Manager'],
            ['MEP-VTS', 'VTS Technician'],

            ['Operations', 'Chief Operating Officer'],
            ['Operations', 'DGM Operations'],
            ['Operations', 'Front Desk Officer'],
            ['Operations', 'GM Ops & Admin'],
            ['Operations', 'Guest Relationship Officer'],
            ['Operations', 'Mall Manager'],
            ['Operations', 'Manager Recovery'],
            ['Operations', 'Operations Officer'],
            ['Operations', 'Shift Incharge'],

            ['Security', 'Security Executive'],
            ['Security', 'Security Supervisor'],
            ['Security', 'Senior Manager Security'],
        ];

        foreach ($records as [$departmentName, $designationName]) {
            $departmentName = trim($departmentName);
            $designationName = trim($designationName);

            $department = DB::table('departments')
                ->where('organization_id', $organization->id)
                ->where('sbu_id', $sbu->id)
                ->whereRaw('TRIM(name) = ?', [$departmentName])
                ->first();

            if (!$department) {
                $this->command->warn("Department not found: {$departmentName}");
                continue;
            }

            DB::table('designations')->updateOrInsert(
                [
                    'organization_id' => $organization->id,
                    'sbu_id' => $sbu->id,
                    'department_id' => $department->id,
                    'name' => $designationName,
                ],
                [
                    'description' => null,
                    'is_active' => 1,
                    'updated_at' => Carbon::now(),
                    'created_at' => Carbon::now(),
                ]
            );
        }

        $this->command->info('Designations imported successfully.');
    }
}