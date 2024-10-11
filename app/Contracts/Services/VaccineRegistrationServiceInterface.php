<?php

namespace App\Contracts\Services;


interface VaccineRegistrationServiceInterface
{
    public function register(array $data);

    public function getVaccinationCenters();

    public function getNextAvailableVaccinationDate($centerId, $startDate = null);

}
