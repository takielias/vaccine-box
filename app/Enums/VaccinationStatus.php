<?php

namespace App\Enums;

use Illuminate\Support\Str;

enum VaccinationStatus: string
{
    case notRegistered = 'Not registered';
    case notScheduled = 'Not scheduled';
    case scheduled = 'Scheduled';
    case vaccinated = 'Vaccinated';

    public static function values(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[$case->value] = $case->getDisplayName();
        }
        return $result;
    }

    /**
     * Get the display-friendly name for the enum case.
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return Str::ucfirst(Str::snake($this->name, ' '));
    }
}
