<?php

namespace App\Contracts\Repositories;

interface VaccineRegistrationRepositoryInterface
{
    function register(array $data);

    function getVaccinationCenters();

    function getVaccinationCountsByDateRange($centerId, $startDate, $endDate);

    function getVaccinationReminderEmails($tomorrow);

    function countPriorVaccinations($vaccination): int;

}
