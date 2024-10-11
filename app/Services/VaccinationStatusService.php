<?php

namespace App\Services;

use App\Contracts\Repositories\VaccinationStatusRepositoryInterface;
use App\Contracts\Services\VaccinationStatusServiceInterface;
use App\Enums\VaccinationStatus;
use Illuminate\Support\Facades\Log;
use Takielias\Lab\Facades\Lab;

class VaccinationStatusService implements VaccinationStatusServiceInterface
{
    public function __construct(public VaccinationStatusRepositoryInterface $vaccinationStatusRepository) {}

    public function getVaccinationStatus($nid)
    {
        try {
            $patient = $this->vaccinationStatusRepository->getVaccinationStatus($nid);

            if (! $patient || ! $patient->vaccination) {
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
            Log::error('Error fetching vaccination status: '.$e->getMessage());

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
