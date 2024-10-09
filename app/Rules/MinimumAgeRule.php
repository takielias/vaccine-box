<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;

class MinimumAgeRule implements ValidationRule
{
    protected mixed $minAge;

    public function __construct($minAge = 18)
    {
        $this->minAge = $minAge;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        //
        $birthDate = Carbon::parse($value);
        $age = $birthDate->age;

        if ($age < $this->minAge) {
            $fail("You must be at least {$this->minAge} years old.");
        }
    }
}
