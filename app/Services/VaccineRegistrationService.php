<?php

namespace App\Services;

use App\Contracts\Repositories\VaccineRegistrationRepositoryInterface;
use App\Contracts\Services\VaccineRegistrationServiceInterface;
use App\Exceptions\NoAvailableDatesException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

readonly class VaccineRegistrationService implements VaccineRegistrationServiceInterface
{

    public function __construct(public VaccineRegistrationRepositoryInterface $vaccineRegistrationRepository)
    {
    }

    /**
     * @throws NoAvailableDatesException
     */
    public function register(array $data)
    {
        $centerId = $data['vaccination_center_id'];
        $nextAvailableDate = $this->getNextAvailableVaccinationDate($centerId);

        if (!$nextAvailableDate) {
            throw new NoAvailableDatesException("No available vaccination dates for the selected center. Please try again later.");
        }

        $data['next_available_date'] = $nextAvailableDate;
        return $this->vaccineRegistrationRepository->register($data);
    }


    function getNextAvailableVaccinationDate($centerId, ?Carbon $startDate = null): ?Carbon
    {
        $startDate = $startDate ?? Carbon::now()->addDay();
        $endDate = $startDate->copy()->addDays(90); // Look up to 90 days in the future

        $center = $this->vaccineRegistrationRepository->getVaccinationCenter($centerId);
        $availableDates = $this->vaccineRegistrationRepository->getVaccinationCountsByDateRange($centerId, $startDate, $endDate);

        // If vaccinations table is empty, return the first available date
        if ($availableDates->isEmpty()) {
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                if ($currentDate->dayOfWeek !== 5 && $currentDate->dayOfWeek !== 6) {
                    return $currentDate;
                }
                $currentDate->addDay();
            }
        }

        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            // Skip Friday (5) and Saturday (6)
            if ($currentDate->dayOfWeek !== 5 && $currentDate->dayOfWeek !== 6) {
                $scheduledCount = $availableDates[$currentDate->toDateString()]->scheduled_count ?? 0;

                if ($scheduledCount < $center->daily_capacity) {
                    return $currentDate;
                }
            }
            $currentDate->addDay();
        }

        return null; // No available dates found within the given range
    }

    public function getVaccinationCenters()
    {
        // TODO: Implement getVaccinationCenters() method.
        return $this->vaccineRegistrationRepository->getVaccinationCenters();
    }
}
