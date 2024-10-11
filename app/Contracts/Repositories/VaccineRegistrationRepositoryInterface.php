<?php

namespace App\Contracts\Repositories;

interface VaccineRegistrationRepositoryInterface
{
    public function register(array $data);

    public function getVaccinationCenters();

    public function getVaccinationCountsByDateRange($centerId, $startDate, $endDate);
}
