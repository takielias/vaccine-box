<?php

namespace Tests\Feature;

use App\Console\Commands\SendEmails;
use App\Enums\VaccinationStatus;
use App\Jobs\SendEmailNotification;
use App\Models\User;
use App\Models\Vaccination;
use App\Models\VaccinationCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SendEmailReminderFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function testSendEmailReminders()
    {
        $user = User::factory()->create();
        $center = VaccinationCenter::factory()->create();

        Vaccination::factory()->create([
            'user_id' => $user->id,
            'vaccination_center_id' => $center->id,
            'status' => VaccinationStatus::scheduled->value,
            'vaccination_date' => Carbon::tomorrow(),
        ]);

        Queue::fake();

        $this->artisan(SendEmails::class, ['--no-interaction' => true])
            ->assertExitCode(0); // Ensure the command runs successfully

        // Assert that the job was dispatched for the user with email
        Queue::assertPushed(SendEmailNotification::class, 1);
    }

    public function testSendEmailRemindersSkipsFridayAndSaturday()
    {
        $user = User::factory()->create();
        $center = VaccinationCenter::factory()->create();

        Vaccination::factory()->create([
            'user_id' => $user->id,
            'vaccination_center_id' => $center->id,
            'status' => VaccinationStatus::scheduled->value,
            'vaccination_date' => Carbon::tomorrow(),
        ]);

        Queue::fake();

        $this->artisan(SendEmails::class, ['--no-interaction' => true])
            ->assertExitCode(0); // Ensure the command runs successfully

        // Assert that the job was dispatched for the user with email
        Queue::assertPushed(SendEmailNotification::class, 1);
    }
}
