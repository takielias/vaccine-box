<?php

namespace App\Http\Controllers;

use App\Contracts\Services\VaccineRegistrationServiceInterface;
use App\Exceptions\NoAvailableDatesException;
use App\Exceptions\RegistrationFailedException;
use App\Http\Requests\VaccineRegistrationRequest;
use App\Http\Requests\ValidVaccinationStatusSearchRequest;
use Takielias\Lab\Facades\Lab;

class VaccinationController extends Controller
{
    public function __construct(private readonly VaccineRegistrationServiceInterface $vaccineRegistration)
    {
    }

    function registration()
    {
        $vaccination_centers = $this->vaccineRegistration->getVaccinationCenters();
        return view('vaccination.registration', compact('vaccination_centers'));
    }

    function proceedRegistration(VaccineRegistrationRequest $request)
    {
        $validated = $request->validated();
        return $this->vaccineRegistration->register($validated);
    }

    function vaccinationStatus()
    {
        return view('vaccination.status');
    }

    function searchVaccinationStatus(ValidVaccinationStatusSearchRequest $request)
    {
        $validated = $request->validated();
        return $this->vaccineRegistration->getVaccinationStatus($validated);
    }
}
