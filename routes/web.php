<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\VaccinationController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web']], function () {
    Route::get('/', [HomeController::class, 'index'])->name('welcome');
    Route::get('/vaccination-status', [HomeController::class, 'vaccinationStatus'])->name('vaccination-status');
    Route::get('/vaccine-registration', [VaccinationController::class, 'registration'])->name('vaccine-registration');
    Route::post('/vaccine-registration', [VaccinationController::class, 'proceedRegistration'])->name('proceed-vaccine-registration');
});
