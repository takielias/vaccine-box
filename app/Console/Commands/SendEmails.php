<?php

namespace App\Console\Commands;

use App\Contracts\Repositories\VaccineRegistrationRepositoryInterface;
use App\Jobs\SendEmailNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendEmails extends Command
{
    protected $signature = 'app:send-emails';
    protected $description = 'Send email notifications to patients with vaccinations scheduled for tomorrow';

    public function __construct(protected readonly VaccineRegistrationRepositoryInterface $vaccinationRegistrationRepository)
    {
        parent::__construct();
    }

    public function handle()
    {
        $tomorrow = now()->addDay();

        $vaccinations = $this->vaccinationRegistrationRepository->getVaccinationReminderEmails($tomorrow);

        $count = 0;

        foreach ($vaccinations as $vaccination) {
            if ($vaccination->user && $vaccination->user->email) {
                SendEmailNotification::dispatch($vaccination);
                $count++;
            } else {
                Log::warning("User or email not found for vaccination ID: {$vaccination->id}");
            }
        }

        $this->info("Dispatched {$count} email notifications.");
    }
}
