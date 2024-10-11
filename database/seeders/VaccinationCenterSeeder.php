<?php

namespace Database\Seeders;

use App\Models\VaccinationCenter;
use Illuminate\Database\Seeder;

class VaccinationCenterSeeder extends Seeder
{
    public function run(): void
    {
        // Create 20 vaccination centers
        //VaccinationCenter::factory()->count(20)->create();

        // Optionally, you can create some centers with specific attributes
        VaccinationCenter::factory()->create([
            'name' => 'Dhaka Medical College & Hospital',
            'daily_capacity' => 5,
        ]);

        VaccinationCenter::factory()->create([
            'name' => 'Shahid Suhrawardy Hospital',
            'daily_capacity' => 5,
        ]);
    }
}
