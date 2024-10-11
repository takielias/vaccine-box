<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vaccination;
use App\Models\VaccinationCenter;
use App\Enums\VaccinationStatus;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Carbon;

class VaccineRegistrationFeatureTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2025-01-01 08:00:00');
    }

    public function testSuccessfulVaccineRegistration()
    {
        $center = VaccinationCenter::factory()->create(['daily_capacity' => 10]);

        $response = $this->postJson('/vaccine-registration', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'birth_date' => '1990-01-01',
            'nid' => '1234567890',
            'phone_number' => '01712345678',
            'vaccination_center_id' => $center->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'success',
                    'user',
                    'vaccination'
                ],
                'message'
            ])
            ->assertJson([
                'data' => [
                    'success' => true,
                ],
                'message' => 'Vaccine registration is Successful.'
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'nid' => '1234567890',
        ]);

        $this->assertDatabaseHas('vaccinations', [
            'vaccination_center_id' => $center->id,
            'status' => VaccinationStatus::scheduled->value,
        ]);
    }

    public function testRegistrationFailsWithInvalidData()
    {
        $center = VaccinationCenter::factory()->create();

        $response = $this->postJson('/vaccine-registration', [
            'name' => '',
            'email' => 'not-an-email',
            'birth_date' => 'invalid-date',
            'nid' => '123', // too short
            'phone_number' => 'not-a-phone-number',
            'vaccination_center_id' => $center->id,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Validation Error.',
                'data' => [
                    'errors' => [
                        'name' => ['Patient Name is required.'],
                        'email' => ['The email field must be a valid email address.'],
                        'nid' => ['Patient NID must be at least 10 characters.'],
                        'birth_date' => [
                            'The birth date field must be a valid date.',
                            'The birth_date is not a valid date.'
                        ],
                        'phone_number' => ['The phone number must be a valid Bangladeshi phone number.']
                    ]
                ]
            ])
            ->assertJsonStructure([
                'fade_out',
                'fade_out_time',
                'redirect_delay',
                'scroll_to_top',
                'top_validation_error',
                'individual_validation_error',
                'message',
                'data' => ['errors'],
                'view',
                'alert'
            ]);
    }

    public function testRegistrationFailsWhenUserAlreadyRegistered()
    {
        $existingUser = User::factory()->create(['nid' => '1234567890']);
        $center = VaccinationCenter::factory()->create();

        $response = $this->postJson('/vaccine-registration', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'birth_date' => '1990-01-01',
            'nid' => '1234567890', // Same NID as existing user
            'phone_number' => '01712345678',
            'vaccination_center_id' => $center->id,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Validation Error.',
                'data' => [
                    'errors' => [
                        'nid' => ['Patient NID already exists.']
                    ]
                ]
            ])
            ->assertJsonStructure([
                'fade_out',
                'fade_out_time',
                'redirect_delay',
                'scroll_to_top',
                'top_validation_error',
                'individual_validation_error',
                'message',
                'data' => ['errors'],
                'view',
                'alert'
            ]);

        // Ensure no new user was created
        $this->assertDatabaseCount('users', 1);
    }

    public function testRegistrationFailsWhenNoAvailableDates()
    {
        $center = VaccinationCenter::factory()->create(['daily_capacity' => 1]);

        // Fill up the center's capacity for the next 90 days
        $startDate = Carbon::now();
        $endDate = $startDate->copy()->addDays(90);
        while ($startDate <= $endDate) {
            if (!in_array($startDate->dayOfWeek, [CarbonInterface::FRIDAY, CarbonInterface::SATURDAY])) {
                User::factory()->create(['nid' => $this->faker->unique()->numerify('##########')]);
                Vaccination::factory()->create([
                    'vaccination_center_id' => $center->id,
                    'vaccination_date' => $startDate,
                    'status' => VaccinationStatus::scheduled->value,
                ]);
            }
            $startDate->addDay();
        }

        $response = $this->postJson('/vaccine-registration', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'birth_date' => '1990-01-01',
            'nid' => '9876543210',
            'phone_number' => '01712345678',
            'vaccination_center_id' => $center->id,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'data' => [
                    'success' => false,
                ],
                'message' => 'No available vaccination dates for the selected center. Please try again later.'
            ]);
    }

    public function testRegistrationWithWeekendStartDate()
    {
        // Set the current date to a Friday
        Carbon::setTestNow('2025-01-03 08:00:00');

        $center = VaccinationCenter::factory()->create(['daily_capacity' => 10]);

        $response = $this->postJson('/vaccine-registration', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'birth_date' => '1990-01-01',
            'nid' => '1234567890',
            'phone_number' => '01712345678',
            'vaccination_center_id' => $center->id,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'success' => true,
                ],
                'message' => 'Vaccine registration is Successful.'
            ])
            ->assertJsonStructure([
                'data' => [
                    'success',
                    'user',
                    'vaccination'
                ],
                'message'
            ]);

        // Parse the vaccination date from the response
        $vaccinationData = $response->json('data.vaccination');
        $scheduledDate = Carbon::parse($vaccinationData['vaccination_date']);

        // Assert that the scheduled date is not a Friday or Saturday
        $this->assertNotContains($scheduledDate->dayOfWeek, [CarbonInterface::FRIDAY, CarbonInterface::SATURDAY],
            "Scheduled date ({$scheduledDate->toDateString()}) should not be Friday or Saturday");

        // Check which day of the week it is
        $dayOfWeek = $scheduledDate->dayOfWeek;
        $dayName = $scheduledDate->format('l');
        $this->assertTrue(in_array($dayOfWeek, [CarbonInterface::SUNDAY, CarbonInterface::MONDAY, CarbonInterface::TUESDAY, CarbonInterface::WEDNESDAY, CarbonInterface::THURSDAY]),
            "Scheduled date ({$scheduledDate->toDateString()}) is on a {$dayName}, which is unexpected");

        // Check if the vaccination record exists in the database
        $this->assertDatabaseHas('vaccinations', [
            'vaccination_center_id' => $center->id,
            'status' => VaccinationStatus::scheduled->value,
        ]);

        // Fetch the actual record from the database
        $vaccinationRecord = Vaccination::where('vaccination_center_id', $center->id)
            ->where('status', VaccinationStatus::scheduled->value)
            ->first();

        $this->assertNotNull($vaccinationRecord, "Vaccination record not found in the database");

        // Compare the dates, ignoring time
        $this->assertTrue(
            $scheduledDate->isSameDay($vaccinationRecord->vaccination_date),
            "Scheduled date ({$scheduledDate->toDateString()}) does not match the date in the database ({$vaccinationRecord->vaccination_date->toDateString()})"
        );

        // Output the scheduled date for debugging
        echo "Scheduled vaccination date: " . $scheduledDate->toDateString() . " ({$dayName})\n";
        echo "Database vaccination date: " . $vaccinationRecord->vaccination_date->toDateString() . "\n";
    }
}
