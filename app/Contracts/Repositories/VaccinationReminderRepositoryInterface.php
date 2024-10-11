<?php

namespace App\Contracts\Repositories;

interface VaccinationReminderRepositoryInterface
{
    public function getVaccinationReminderData($tomorrow);

    public function countPriorVaccinations($vaccination): int;
}
