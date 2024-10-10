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
            'name' => 'Central City Vaccination Hub',
            'daily_capacity' => 1,
        ]);

        VaccinationCenter::factory()->create([
            'name' => 'Suburban Health Clinic',
            'daily_capacity' => 1,
        ]);
    }
}
