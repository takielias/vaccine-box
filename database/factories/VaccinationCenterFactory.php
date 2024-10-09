<?php

namespace Database\Factories;

use App\Models\VaccinationCenter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VaccinationCenter>
 */
class VaccinationCenterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = VaccinationCenter::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company . ' Vaccination Center',
            'daily_capacity' => $this->faker->numberBetween(5, 100),
        ];
    }
}
