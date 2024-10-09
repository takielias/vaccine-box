<?php

namespace App\Contracts\Repositories;

use App\Models\VaccinationCenter;
use Illuminate\Support\Carbon;

interface VaccineRegistrationRepositoryInterface
{
    function register(array $data);

    function getVaccinationCenters();

    function getNextAvailableVaccinationDate($centerId, ?Carbon $startDate = null): ?Carbon;
}
