<?php

namespace Farapayamak\Laravel\Support;

class PhoneNumber
{
    /**
     * Persian and Arabic digits to English digits mapping.
     */
    protected static array $faToEn = [
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
    ];

    /**
     * Sanitize and format a phone number to standard Iranian mobile format (09xxxxxxxxx).
     */
    public static function clean(?string $number): string
    {
        if (empty($number)) {
            return '';
        }

        // Convert Persian/Arabic digits to English
        $number = strtr((string) $number, self::$faToEn);

        // Strip non-digit characters
        $number = preg_replace('/[^\d]/', '', $number);

        if (empty($number)) {
            return '';
        }

        // Handle prefixes like 0098 or 98
        if (str_starts_with($number, '0098')) {
            $number = '0' . substr($number, 4);
        } elseif (str_starts_with($number, '98') && strlen($number) === 12) {
            $number = '0' . substr($number, 2);
        } elseif (str_starts_with($number, '9') && strlen($number) === 10) {
            $number = '0' . $number;
        }

        return $number;
    }

    /**
     * Validate whether the number is a valid 11-digit Iranian mobile number.
     */
    public static function isValid(string $number): bool
    {
        $cleaned = self::clean($number);
        return (bool) preg_match('/^09[0-9]{9}$/', $cleaned);
    }

    /**
     * Clean an array or comma-separated list of mobile numbers.
     *
     * @param array|string $numbers
     * @return array<string>
     */
    public static function cleanMultiple(array|string $numbers): array
    {
        if (is_string($numbers)) {
            $numbers = preg_split('/[\s,]+/', $numbers, -1, PREG_SPLIT_NO_EMPTY);
        }

        return array_values(array_filter(array_map([self::class, 'clean'], $numbers)));
    }
}
