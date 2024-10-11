<?php

namespace App\Contracts\Repositories;

interface VaccinationStatusRepositoryInterface
{
    function getVaccinationStatus($nid);
}
