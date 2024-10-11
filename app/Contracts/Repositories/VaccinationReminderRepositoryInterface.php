<?php

namespace App\Contracts\Repositories;

interface VaccinationReminderRepositoryInterface
{
    function getVaccinationReminderEmails($tomorrow);

    function countPriorVaccinations($vaccination): int;

}
