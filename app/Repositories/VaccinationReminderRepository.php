<?php

namespace App\Repositories;

use App\Contracts\Repositories\VaccinationReminderRepositoryInterface;
use App\Enums\VaccinationStatus;
use App\Models\Vaccination;

class VaccinationReminderRepository implements VaccinationReminderRepositoryInterface
{
    function getVaccinationReminderEmails($tomorrow)
    {
        return Vaccination::with('user')
            ->whereDate('vaccination_date', $tomorrow->toDateString())
            ->where('status', VaccinationStatus::scheduled->value)
            ->get();
    }

    public function countPriorVaccinations($vaccination): int
    {
        return Vaccination::where('vaccination_center_id', $vaccination->vaccination_center_id)
            ->whereDate('vaccination_date', $vaccination->vaccination_date)
            ->where('id', '<', $vaccination->id)
            ->count();
    }

}
