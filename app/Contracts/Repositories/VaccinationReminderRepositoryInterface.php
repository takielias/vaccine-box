<?php

namespace App\Contracts\Repositories;

interface VaccinationReminderRepositoryInterface
{
    public function getVaccinationReminderEmails($tomorrow);

    public function countPriorVaccinations($vaccination): int;
}
