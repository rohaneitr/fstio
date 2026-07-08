<?php

declare(strict_types=1);

namespace App\Foundation\Geography;

enum Division: string
{
    case DHAKA = 'Dhaka';
    case CHATTOGRAM = 'Chattogram';
    case RAJSHAHI = 'Rajshahi';
    case KHULNA = 'Khulna';
    case BARISHAL = 'Barishal';
    case SYLHET = 'Sylhet';
    case RANGPUR = 'Rangpur';
    case MYMENSINGH = 'Mymensingh';

    /**
     * Get all Division values.
     *
     * @return array<int, string>
     */
    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
