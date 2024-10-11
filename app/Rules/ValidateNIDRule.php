<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateNIDRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            $fail('Patient NID is required.');
        }

        if (! is_numeric($value)) {
            $fail('Patient NID must be numeric.');
        }

        if (strlen($value) < 10) {
            $fail('Patient NID must be at least 10 characters.');
        }

        if (! preg_match('/^[a-zA-Z0-9]+$/', $value)) {
            $fail('Patient NID is invalid.');
        }
    }
}
