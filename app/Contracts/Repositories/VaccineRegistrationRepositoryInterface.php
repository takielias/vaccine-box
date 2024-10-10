<?php

namespace App\Contracts\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

interface VaccineRegistrationRepositoryInterface
{
    function register(array $data);

    function getVaccinationCenters();

    function getVaccinationCountsByDateRange($centerId, Carbon $startDate, Carbon $endDate): Collection;

    function getVaccinationStatus($nid);
}
