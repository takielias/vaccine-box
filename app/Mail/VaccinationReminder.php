<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VaccinationReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $vaccination;
    public $timeSlot;

    public function __construct($vaccination, string $timeSlot)
    {
        $this->vaccination = $vaccination;
        $this->timeSlot = $timeSlot;
    }

    public function build()
    {
        return $this->view('emails.vaccination-reminder')
            ->subject('Important: Your Vaccination Appointment Tomorrow')
            ->with([
                'timeSlot' => $this->timeSlot
            ]);
    }
}
