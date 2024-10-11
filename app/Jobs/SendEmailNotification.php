<?php

namespace App\Jobs;

use App\Contracts\Repositories\VaccinationReminderRepositoryInterface;
use App\Mail\VaccinationReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class SendEmailNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $vaccination;

    public function __construct($vaccination)
    {
        $this->vaccination = $vaccination;
    }

    public function handle(VaccinationReminderRepositoryInterface $vaccineRegistrationRepository): void
    {
        $user = $this->vaccination->user;

        if ($user && $user->email) {
            $timeSlot = $this->calculateTimeSlot($vaccineRegistrationRepository);
            Mail::to($user->email)->send(new VaccinationReminder($this->vaccination, $timeSlot));
        }
    }

    protected function calculateTimeSlot(VaccinationReminderRepositoryInterface $repository): string
    {
        $startTime = Carbon::create(2024, 1, 1, 9, 0, 0); // 9:00 AM
        $vaccinationsBeforeThis = $repository->countPriorVaccinations($this->vaccination);
        $slotTime = $startTime->addMinutes($vaccinationsBeforeThis * 5);
        return $slotTime->format('g:i A');
    }
}
