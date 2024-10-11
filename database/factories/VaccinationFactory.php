<?php

namespace Database\Factories;

use App\Enums\VaccinationStatus;
use App\Models\User;
use App\Models\Vaccination;
use App\Models\VaccinationCenter;
use Illuminate\Database\Eloquent\Factories\Factory;

class VaccinationFactory extends Factory
{
    protected $model = Vaccination::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'vaccination_center_id' => VaccinationCenter::factory(),
            'vaccination_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'status' => $this->faker->randomElement(VaccinationStatus::cases())->value,
        ];
    }

    public function scheduled()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => VaccinationStatus::scheduled->value,
            ];
        });
    }

    public function vaccinated()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => VaccinationStatus::vaccinated->value,
                'vaccination_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            ];
        });
    }
}
