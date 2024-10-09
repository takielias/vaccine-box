<?php

namespace App\Providers;

use App\Contracts\Repositories\VaccineRegistrationRepositoryInterface;
use App\Contracts\Services\VaccineRegistrationServiceInterface;
use App\Repositories\VaccineRegistrationRepository;
use App\Services\VaccineRegistrationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

        $this->app->bind(VaccineRegistrationServiceInterface::class, VaccineRegistrationService::class);
        $this->app->bind(VaccineRegistrationRepositoryInterface::class, VaccineRegistrationRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
