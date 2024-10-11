<?php

if (! function_exists('formatPhoneNumber')) {
    function formatPhoneNumber($phone): string
    {
        // Check if the phone number contains only digits, plus sign, hyphens, and spaces
        if (! preg_match('/^[0-9+\s-]+$/', $phone)) {
            return 'NO';
        }
        // Remove any non-numeric characters
        $phone = preg_replace('/\D/', '', $phone);
        // Ensure the remaining string is numeric
        if (! is_numeric($phone)) {
            return 'NO';
        }
        // Check if the phone contains a valid prefix followed by 8 digits
        $pattern = '/.*(1[3-9]\d{8}).*/';
        if (preg_match($pattern, $phone, $matches)) {
            // Return the formatted number with "880" prepended
            return '880'.$matches[1];
        }

        return 'NO';
    }
}
