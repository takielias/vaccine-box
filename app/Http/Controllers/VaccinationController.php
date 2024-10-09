<?php

namespace App\Http\Controllers;

use App\Contracts\Services\VaccineRegistrationServiceInterface;
use App\Exceptions\NoAvailableDatesException;
use App\Exceptions\RegistrationFailedException;
use App\Http\Requests\VaccineRegistrationRequest;
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
        try {
            $result = $this->vaccineRegistration->register($validated);
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
}
