<?php

namespace App\Contracts\Services;

interface VaccinationReminderServiceInterface
{
    public function getVaccinationStatus($nid);
}
