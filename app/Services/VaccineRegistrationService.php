<?php

namespace App\Services;

use App\Contracts\Repositories\VaccineRegistrationRepositoryInterface;
use App\Contracts\Services\VaccineRegistrationServiceInterface;
use App\Enums\VaccinationStatus;
use App\Exceptions\NoAvailableDatesException;
use App\Exceptions\RegistrationFailedException;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Takielias\Lab\Facades\Lab;

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
        } catch (NoAvailableDatesException $e) {
            return Lab::setData(['success' => false])
                ->enableScrollToTop()
                ->setDanger($e->getMessage())
                ->setStatus(400)
                ->toJsonResponse();
        } catch (RegistrationFailedException $e) {
            return Lab::setData(['success' => false])
                ->enableScrollToTop()
                ->setDanger($e->getMessage())
                ->setStatus(500)
                ->toJsonResponse();
        }
    }


    function getNextAvailableVaccinationDate($centerId, ?Carbon $startDate = null): ?Carbon
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

    public function getVaccinationStatus($nid)
    {
        // TODO: Implement getStatus() method.
        try {
            $patient = $this->vaccineRegistrationRepository->getVaccinationStatus($nid);

            if (!$patient || !$patient->vaccination) {
                $registrationLink = route('vaccine-registration');
                return $this->formatResponse(
                    VaccinationStatus::notRegistered,
                    "You are not registered for vaccination. Please <a class='alert-link' href='{$registrationLink}'><b>Register Here</b></a>",
                    400
                );
            }

            $status = $this->determineVaccinationStatus($patient->vaccination);
            $scheduledDate = $patient->vaccination->vaccination_date->format('d-m-Y');

            $message = $this->getStatusMessage($status, $scheduledDate);

            return $this->formatResponse($status, $message, 200);

        } catch (\Exception $e) {
            Log::error('Error fetching vaccination status: ' . $e->getMessage());
            return $this->formatResponse(
                null,
                'An error occurred while fetching the vaccination status.',
                500
            );
        }
    }

    private function determineVaccinationStatus($vaccination): VaccinationStatus
    {
        if ($vaccination->vaccination_date->isPast()) {
            return VaccinationStatus::vaccinated;
        }
        return VaccinationStatus::from($vaccination->status);
    }

    private function getStatusMessage(VaccinationStatus $status, string $date): string
    {
        return match ($status) {
            VaccinationStatus::notScheduled => 'Your vaccination is not yet scheduled.',
            VaccinationStatus::scheduled => "Your vaccination is scheduled for <b>{$date}</b>.",
            VaccinationStatus::vaccinated => "Your vaccination was completed on <b>{$date}</b>.",
            default => 'Unknown vaccination status.',
        };
    }

    private function formatResponse(?VaccinationStatus $status, string $message, int $httpStatus)
    {
        $lab = Lab::setData([
            'success' => $httpStatus < 400,
            'status' => $status?->value,
        ])
            ->enableScrollToTop()
            ->setStatus($httpStatus)
            ->setFadeOutTime(5000)
            ->setRedirectDelay(5500);
        match ($status) {
            VaccinationStatus::notScheduled,
            VaccinationStatus::scheduled => $lab->setInfo($message)->setIconClass('ti ti-mood-happy'),
            VaccinationStatus::vaccinated => $lab->setSuccess($message),
            default => $lab->setWarning($message),
        };
        if ($httpStatus >= 400) {
            $lab->setDanger($message);
        }
        return $lab->toJsonResponse();
    }

}
