<?php

declare(strict_types=1);

namespace App\Foundation\Helpers;

use Illuminate\Support\Str;

class FoundationHelper
{
    /**
     * Generate a unique url slug.
     */
    public static function slugify(string $title): string
    {
        return Str::slug($title);
    }

    /**
     * Format money/price values.
     */
    public static function formatMoney(float $amount): string
    {
        return '৳'.number_format($amount, 2);
    }

    /**
     * Clean and format Bangladesh phone numbers.
     */
    public static function formatBangladeshPhone(string $phone): string
    {
        // Remove non-digit characters
        $digits = preg_replace('/\D/', '', $phone);

        // Strip country code prefixes if present
        if (str_starts_with($digits, '880')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0')) {
            // Keep it
        } else {
            $digits = '0'.$digits;
        }

        return $digits;
    }

    /**
     * Format dates into standard BD commerce format (e.g. DD-MM-YYYY).
     */
    public static function formatDate(string $date): string
    {
        return date('d-m-Y', strtotime($date));
    }
}
