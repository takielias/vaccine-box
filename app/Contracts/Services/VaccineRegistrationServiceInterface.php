<?php

namespace App\Contracts\Services;

use Illuminate\Support\Carbon;

interface VaccineRegistrationServiceInterface
{
    public function register(array $data);

    public function getVaccinationCenters();

    public function getNextAvailableVaccinationDate($centerId, ?Carbon $startDate = null): ?Carbon;
}
