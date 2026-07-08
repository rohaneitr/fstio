<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use App\Foundation\Geography\Division;

final readonly class Address
{
    public function __construct(
        private string $streetAddress,
        private string $upazila,
        private string $district,
        private Division $division,
        private string $postalCode
    ) {}

    public function getStreetAddress(): string
    {
        return $this->streetAddress;
    }

    public function getUpazila(): string
    {
        return $this->upazila;
    }

    public function getDistrict(): string
    {
        return $this->district;
    }

    public function getDivision(): Division
    {
        return $this->division;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getFormatted(): string
    {
        return sprintf(
            '%s, %s, %s, %s - %s',
            $this->streetAddress,
            $this->upazila,
            $this->district,
            $this->division->value,
            $this->postalCode
        );
    }
}
