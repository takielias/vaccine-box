<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;

class ValidatePhoneNumberRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        Log::info($value);
        if (formatPhoneNumber($value) == 'NO') {
            $fail('The :attribute must be a valid Bangladeshi phone number.');
        }
    }
}
