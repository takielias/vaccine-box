<?php

namespace App\Repositories;

use App\Contracts\Repositories\VaccineRegistrationRepositoryInterface;
use App\Exceptions\RegistrationFailedException;
use App\Models\User;
use App\Models\Vaccination;
use App\Models\VaccinationCenter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VaccineRegistrationRepository implements VaccineRegistrationRepositoryInterface
{

    /**
     * @throws RegistrationFailedException
     */
    function register(array $data)
    {
        try {
            return DB::transaction(function () use ($data) {
                // Create or update user
                $randomPassword = Str::random(8);
                $user = User::create(
                    [
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'birth_date' => $data['birth_date'],
                        'nid' => $data['nid'],
                        'phone_number' => $data['phone_number'] ?? null,
                        'password' => Hash::make($randomPassword),
                    ]
                );

                // Create vaccination record
                $vaccination = Vaccination::create([
                    'user_id' => $user->id,
                    'vaccination_center_id' => $data['vaccination_center_id'],
                    'vaccination_date' => $data['next_available_date'],
                    'status' => 'scheduled', // Assuming you have a status field
                ]);

                return [
                    'user' => $user,
                    'vaccination' => $vaccination,
                ];
            });
        } catch (\Exception $e) {
            throw new RegistrationFailedException("Failed to register for vaccination: " . $e->getMessage());
        }
    }

    function getVaccinationCenters()
    {
        // TODO: Implement getVaccinationCenters() method.
        return VaccinationCenter::all()->pluck('name', 'id')->prepend('Please Select Center', '');
    }

    function getNextAvailableVaccinationDate($centerId, ?Carbon $startDate = null): ?Carbon
    {
        $center = VaccinationCenter::findOrFail($centerId);
        $startDate = $startDate ?? Carbon::now()->addDays(1);
        $endDate = $startDate->copy()->addDays(90);

        $availableDates = DB::table('vaccinations')
            ->select(DB::raw('vaccination_date, COUNT(*) as scheduled_count'))
            ->where('vaccination_center_id', $center->id)
            ->whereBetween('vaccination_date', [$startDate, $endDate])
            ->groupBy('vaccination_date')
            ->havingRaw('scheduled_count < ?', [$center->daily_capacity])
            ->orderBy('vaccination_date')
            ->get();

        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            // Skip Friday (5) and Saturday (6)
            if ($currentDate->dayOfWeek !== 5 && $currentDate->dayOfWeek !== 6) {
                $scheduledCount = $availableDates->firstWhere('vaccination_date', $currentDate->toDateString())?->scheduled_count ?? 0;
                if ($scheduledCount < $center->daily_capacity) {
                    return $currentDate;
                }
            }
            $currentDate->addDay();
        }

        return null; // No available dates found within the given range
    }

}
