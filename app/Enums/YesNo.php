<?php

namespace App\Enums;

use Illuminate\Support\Collection;

enum YesNo: string
{
    case yes = 'yes';
    case no = 'no';

    public static function values(): Collection
    {
        return collect(self::cases())->mapWithKeys(function ($case) {
            return [$case->value => $case->name];
        });
    }
}
