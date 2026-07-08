<?php

declare(strict_types=1);

namespace App\Foundation\Localization;

use App\Foundation\Contracts\ServiceInterface;
use Illuminate\Support\Facades\App;

class LocalizationService implements ServiceInterface
{
    /**
     * Get the active locale code.
     */
    public function getLocale(): string
    {
        return App::getLocale();
    }

    /**
     * Get the active currency code.
     */
    public function getCurrency(): string
    {
        return 'BDT';
    }

    /**
     * Get target timezone.
     */
    public function getTimezone(): string
    {
        return 'Asia/Dhaka';
    }

    /**
     * Format a price to Bangladesh Taka currency display format.
     */
    public function formatPrice(float $amount): string
    {
        return '৳'.number_format($amount, 2);
    }

    /**
     * Get list of Bangladesh administrative divisions.
     *
     * @return array<int, string>
     */
    public function getBangladeshDivisions(): array
    {
        return [
            'Dhaka',
            'Chattogram',
            'Rajshahi',
            'Khulna',
            'Barishal',
            'Sylhet',
            'Rangpur',
            'Mymensingh',
        ];
    }
}
