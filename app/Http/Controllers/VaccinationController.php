<?php

namespace App\Http\Controllers;

use App\Contracts\Services\VaccinationStatusServiceInterface;
use App\Contracts\Services\VaccineRegistrationServiceInterface;
use App\Http\Requests\VaccineRegistrationRequest;
use App\Http\Requests\ValidVaccinationStatusSearchRequest;

class VaccinationController extends Controller
{
    public function __construct(
        private readonly VaccineRegistrationServiceInterface $vaccineRegistration,
        private readonly VaccinationStatusServiceInterface $vaccinationStatusService
    ) {}

    public function registration()
    {
        $vaccination_centers = $this->vaccineRegistration->getVaccinationCenters();

        return view('vaccination.registration', compact('vaccination_centers'));
    }

    public function proceedRegistration(VaccineRegistrationRequest $request)
    {
        $validated = $request->validated();

        return $this->vaccineRegistration->register($validated);
    }

    public function vaccinationStatus()
    {
        return view('vaccination.status');
    }

    public function searchVaccinationStatus(ValidVaccinationStatusSearchRequest $request)
    {
        $validated = $request->validated();

        return $this->vaccinationStatusService->getVaccinationStatus($validated);
    }
}
