<?php

namespace App\Contracts\Repositories;

interface VaccinationStatusRepositoryInterface
{
    public function getVaccinationStatus($nid);
}
