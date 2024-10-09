<?php

namespace App\Services;

use App\Contracts\Repositories\VaccineRegistrationRepositoryInterface;
use App\Contracts\Services\VaccineRegistrationServiceInterface;
use App\Exceptions\NoAvailableDatesException;
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
        Log::info(json_encode($data));
        $nextAvailableDate = $this->vaccineRegistrationRepository->getNextAvailableVaccinationDate($centerId);
        if (!$nextAvailableDate) {
            throw new NoAvailableDatesException("No available vaccination dates for the selected center.");
        }
        Log::info(json_encode($nextAvailableDate));
        $data['next_available_date'] = $nextAvailableDate;

       return $this->vaccineRegistrationRepository->register($data);
    }

    public function getVaccinationCenters()
    {
        // TODO: Implement getVaccinationCenters() method.
        return $this->vaccineRegistrationRepository->getVaccinationCenters();
    }
}
