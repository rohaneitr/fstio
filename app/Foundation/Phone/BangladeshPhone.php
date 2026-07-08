<?php

declare(strict_types=1);

namespace App\Foundation\Phone;

use InvalidArgumentException;

final readonly class BangladeshPhone
{
    private string $normalizedNumber;

    public function __construct(string $number)
    {
        $this->normalizedNumber = $this->normalize($number);

        if (! $this->isValid($this->normalizedNumber)) {
            throw new InvalidArgumentException('Invalid Bangladesh phone number format.');
        }
    }

    public function getNormalized(): string
    {
        return $this->normalizedNumber;
    }

    public function getFormatted(): string
    {
        // Format: +880 1759-190782
        return '+880 '.substr($this->normalizedNumber, 1, 4).'-'.substr($this->normalizedNumber, 5);
    }

    private function normalize(string $number): string
    {
        $digits = preg_replace('/\D/', '', $number);

        if (str_starts_with($digits, '880')) {
            $digits = substr($digits, 2);
        }

        if (! str_starts_with($digits, '0') && strlen($digits) === 10) {
            $digits = '0'.$digits;
        }

        return $digits;
    }

    private function isValid(string $number): bool
    {
        return (bool) preg_match('/^01[3-9]\d{8}$/', $number);
    }
}
