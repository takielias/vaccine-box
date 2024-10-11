<?php

namespace Tests\Feature;

use App\Enums\VaccinationStatus;
use App\Models\User;
use App\Models\Vaccination;
use App\Models\VaccinationCenter;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VaccinationStatusFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function testCheckStatusForScheduledVaccination()
    {
        $user = User::factory()->create();
        $center = VaccinationCenter::factory()->create();
        Vaccination::factory()->create([
            'user_id' => $user->id,
            'vaccination_center_id' => $center->id,
            'status' => VaccinationStatus::scheduled->value,
            'vaccination_date' => Carbon::tomorrow(),
        ]);

        $response = $this->postJson('/vaccination-status', [
            'nid' => $user->nid,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'success',
                    'status',
                ],
            ])
            ->assertJson([
                'data' => [
                    'success' => true,
                    'status' => VaccinationStatus::scheduled->getDisplayName(),
                ]]);

    }

    public function testCheckStatusForVaccinatedUser()
    {
        $user = User::factory()->create();
        $center = VaccinationCenter::factory()->create();
        Vaccination::factory()->create([
            'user_id' => $user->id,
            'vaccination_center_id' => $center->id,
            'status' => VaccinationStatus::scheduled->value,
            'vaccination_date' => Carbon::yesterday(),
        ]);

        $response = $this->postJson('/vaccination-status', [
            'nid' => $user->nid,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'success',
                    'status',
                ],
            ])
            ->assertJson([
                'data' => [
                    'success' => true,
                    'status' => VaccinationStatus::vaccinated->getDisplayName(),
                ]]);

    }

    public function testCheckStatusForNonRegisteredUser()
    {
        $response = $this->postJson('/vaccination-status', [
            'nid' => '1234567890',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Validation Error.',
                'data' => [
                    'errors' => [
                        'nid' => ["The provided NID is not registered in our system. Please <a class='btn btn-sm btn-ghost-primary' href='http://localhost/vaccine-registration'>Register Here</a>"],
                    ],
                ],
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
                'alert',
            ]);

    }

    public function testCheckStatusWithInvalidNID()
    {
        $response = $this->postJson('/vaccination-status', [
            'nid' => '123@123',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Validation Error.',
                'data' => [
                    'errors' => [
                        'nid' => [
                            'Patient NID must be numeric.',
                            'Patient NID must be at least 10 characters.',
                            'Patient NID is invalid.',
                        ],
                    ],
                ],
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
                'alert',
            ]);
    }
}
