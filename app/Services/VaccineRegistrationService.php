<?php

namespace App\Services;

use App\Contracts\Repositories\VaccineRegistrationRepositoryInterface;
use App\Contracts\Services\VaccineRegistrationServiceInterface;
use App\Exceptions\NoAvailableDatesException;
use App\Exceptions\RegistrationFailedException;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Takielias\Lab\Facades\Lab;

class VaccineRegistrationService implements VaccineRegistrationServiceInterface
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
            return Lab::setData(['success' => false])
                ->enableScrollToTop()
                ->setDanger("No available vaccination dates for the selected center. Please try again later.")
                ->setStatus(400)
                ->toJsonResponse();
        }

        $data['next_available_date'] = $nextAvailableDate;

        try {
            $result = $this->vaccineRegistrationRepository->register($data);
            return Lab::setData([
                'success' => true,
                'user' => $result['user'],
                'vaccination' => $result['vaccination']
            ])
                ->enableScrollToTop()
                ->setSuccess('Vaccine registration is Successful.')
                ->setStatus(201)
                ->setRedirect(route('welcome'))
                ->setFadeOutTime(5000)
                ->setRedirectDelay(5500)
                ->toJsonResponse();
        } catch (RegistrationFailedException $e) {
            return Lab::setData(['success' => false])
                ->enableScrollToTop()
                ->setDanger($e->getMessage())
                ->setStatus(500)
                ->toJsonResponse();
        }
    }

    function getNextAvailableVaccinationDate($centerId, $startDate = null)
    {

        $now = Carbon::now();
        $startDate = $startDate ?? $now;

        if ($now->hour >= 9) {
            $startDate = $startDate->addDay();
        }

        // Skip Friday (5) and Saturday (6)
        while ($startDate->dayOfWeek === CarbonInterface::FRIDAY || $startDate->dayOfWeek === CarbonInterface::SATURDAY) {
            $startDate->addDay();
        }

        $endDate = $startDate->copy()->addDays((int)env('VACCINE_SCHEDULE_WINDOW_DAYS', 90)); // Look up to 90 days in the future

        $center = $this->vaccineRegistrationRepository->getVaccinationCenter($centerId);
        $availableDates = $this->vaccineRegistrationRepository->getVaccinationCountsByDateRange($centerId, $startDate, $endDate);

        // If vaccinations table is empty, return the first available date
        if ($availableDates->isEmpty()) {
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                if ($currentDate->dayOfWeek !== CarbonInterface::FRIDAY && $currentDate->dayOfWeek !== CarbonInterface::SATURDAY) {
                    return $currentDate;
                }
                $currentDate->addDay();
            }
        }

        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            // Skip Friday (5) and Saturday (6)
            if ($currentDate->dayOfWeek !== CarbonInterface::FRIDAY && $currentDate->dayOfWeek !== CarbonInterface::SATURDAY) {
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
        return $this->vaccineRegistrationRepository->getVaccinationCenters();
    }

}
