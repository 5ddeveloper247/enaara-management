<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\Province;
use App\Models\District;

class AjkDistrictsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get Country: Pakistan
        $country = Country::where('name', 'Pakistan')->first();

        if (!$country) {
            $this->command->error("Country 'Pakistan' not found. Please ensure countries are seeded.");
            return;
        }

        // Get Province: Azad Kashmir
        $province = Province::where('country_id', $country->id)
            ->where('name', 'Azad Kashmir')
            ->first();

        if (!$province) {
            $this->command->error("Province 'Azad Kashmir' not found for Pakistan. Please ensure provinces are seeded.");
            return;
        }

        // Districts list for Azad Kashmir
        $districts = [
            'Muzaffarabad',
            'Neelum',
            'Jhelum Valley',
            'Bagh',
            'Haveli',
            'Poonch',
            'Sudhnuti',
            'Kotli',
            'Mirpur',
            'Patika (Naseerabad)',
            'Athmuqam',
            'Hattian Bala',
            'Chikar',
            'Leepa',
            'Harigehl',
            'Dhirkot',
            'Khurshidabad',
            'Mumtazabad',
            'Thorar',
            'Rawalakot',
            'Hajira',
            'Abbaspur',
            'Pallandri',
            'Mong',
            'Trarkhal',
            'Baloch',
            'Sehnsa',
            'Fatehpur',
            'Charhoi',
            'Duliah Jattan',
            'Khuiratta',
            'Dadyal',
            'Samahni',
            'Barnala',
        ];

        // Insert districts avoiding duplicates
        foreach ($districts as $districtName) {
            District::firstOrCreate(
                [
                    'province_id' => $province->id,
                    'name' => $districtName,
                ],
                [
                    'country_id' => $country->id,
                    'is_active' => 1,
                ]
            );
        }

        $this->command->info('Azad Kashmir districts have been seeded successfully.');
    }
}
