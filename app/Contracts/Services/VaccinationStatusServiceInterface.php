<?php

namespace App\Contracts\Services;

interface VaccinationStatusServiceInterface
{
    public function getVaccinationStatus($nid);
}
