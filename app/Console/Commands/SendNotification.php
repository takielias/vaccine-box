<?php

namespace App\Console\Commands;

use App\Contracts\Repositories\VaccinationReminderRepositoryInterface;
use App\Jobs\SendEmailNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendNotification extends Command
{
    protected $signature = 'app:send-notification';

    protected $description = 'Send notifications to patients with vaccinations scheduled for tomorrow';

    public function __construct(protected readonly VaccinationReminderRepositoryInterface $vaccinationReminderRepository)
    {
        parent::__construct();
    }

    public function handle()
    {
        $tomorrow = now()->addDay();

        $vaccinations = $this->vaccinationReminderRepository->getVaccinationReminderData($tomorrow);

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
