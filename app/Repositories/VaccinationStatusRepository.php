<?php

namespace App\Repositories;

use App\Contracts\Repositories\VaccinationStatusRepositoryInterface;
use App\Models\User;

class VaccinationStatusRepository implements VaccinationStatusRepositoryInterface
{
    public function getVaccinationStatus($nid)
    {
        return User::whereNid($nid)->first();
    }
}
