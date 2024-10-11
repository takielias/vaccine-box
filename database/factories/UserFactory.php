<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'nid' => $this->generateUniqueNid(),
            'birth_date' => $this->faker->date('Y-m-d', '-18 years'),
            'phone_number' => $this->generateBangladeshiPhoneNumber(),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function minor(): static
    {
        return $this->state(fn (array $attributes) => [
            'birth_date' => $this->faker->date('Y-m-d', '-17 years'),
        ]);
    }

    public function senior(): static
    {
        return $this->state(fn (array $attributes) => [
            'birth_date' => $this->faker->date('Y-m-d', '-60 years'),
        ]);
    }

    protected function generateUniqueNid(): string
    {
        return $this->faker->unique()->numerify('##########');
    }

    protected function generateBangladeshiPhoneNumber(): string
    {
        $prefixes = ['013', '014', '015', '016', '017', '018', '019'];
        $prefix = $this->faker->randomElement($prefixes);
        $number = $this->faker->numerify('########');

        return $prefix.$number;
    }
}
