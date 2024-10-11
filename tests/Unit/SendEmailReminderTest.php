<?php

namespace Tests\Unit;

use App\Console\Commands\SendEmails;
use App\Contracts\Repositories\VaccinationReminderRepositoryInterface;
use App\Jobs\SendEmailNotification;
use Illuminate\Console\OutputStyle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

class SendEmailReminderTest extends TestCase
{
    use RefreshDatabase;

    public function testHandleSendsEmailNotifications()
    {
        $vaccinationReminderRepository = Mockery::mock(VaccinationReminderRepositoryInterface::class);
        $vaccinationDate = Carbon::now()->addDay();
        $vaccinationReminderRepository->shouldReceive('getVaccinationReminderEmails')
            ->andReturn(collect([
                (object) ['id' => 1, 'user' => (object) ['email' => 'test@example.com'], 'vaccination_date' => $vaccinationDate],
            ]));

        Queue::fake();

        $output = new BufferedOutput;
        $input = new ArgvInput;
        $outputStyle = new OutputStyle($input, $output);
        $command = new SendEmails($vaccinationReminderRepository);
        $command->setOutput($outputStyle);
        $command->handle();

        // Assert that the SendEmailNotification job was pushed exactly once (for the user with email)
        Queue::assertPushed(SendEmailNotification::class, 1);
    }
}
