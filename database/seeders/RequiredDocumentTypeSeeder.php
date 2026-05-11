<?php

namespace Database\Seeders;

use App\Models\RequiredDocumentType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RequiredDocumentTypeSeeder extends Seeder
{
    /**
     * Wipes existing required document types and inserts the canonical list.
     * Safe to re-run on live — existing rows are truncated first.
     */
    public function run(): void
    {
        // Disable FK checks to allow truncate (table has no FK dependencies)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        RequiredDocumentType::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $types = [
            'Employee CNIC (Front)',
            'Employee CNIC (Back)',
            "Father's CNIC",
            'NOK CNIC',
            'CV / Resume',
            'Offer / Appointment Letter',
            'Police Verification Letter',
            'Probation Evaluation Report',
            'Consultancy Agreement / Contract',
            'Parent/Guardian Consent Form',
            'Discharge / Retirement Order',
        ];

        foreach ($types as $name) {
            RequiredDocumentType::create([
                'name'   => $name,
                'status' => true,
            ]);
        }

        $this->command->info('RequiredDocumentTypeSeeder: ' . count($types) . ' types seeded successfully.');
    }
}
