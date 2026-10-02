<?php

namespace App\Support;

use InvalidArgumentException;

class Phone
{
    public const VALIDATION_PATTERN = '/^(\+62|62|0)8\d{8,11}$/';

    /**
     * Kanonikkan nomor HP Indonesia ke format 62xxxxxxxxxx.
     */
    public static function normalize(string $raw): string
    {
        if (!preg_match(self::VALIDATION_PATTERN, $raw)) {
            throw new InvalidArgumentException('Nomor HP harus format Indonesia, contoh: 081234567890');
        }

        $digits = preg_replace('/\D/', '', $raw);

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        return $digits;
    }
}
