<?php

declare(strict_types=1);

namespace App\Foundation\Geography;

final class GeoResolver
{
    /**
     * Get districts map for a given division.
     *
     * @return array<int, string>
     */
    public function getDistricts(Division $division): array
    {
        return match ($division) {
            Division::DHAKA => ['Dhaka', 'Gazipur', 'Narayanganj', 'Tangail', 'Faridpur'],
            Division::CHATTOGRAM => ['Chattogram', 'Cox\'s Bazar', 'Cumilla', 'Feni', 'Noakhali'],
            default => ['Other District'],
        };
    }

    /**
     * Get upazilas map for a given district.
     *
     * @return array<int, string>
     */
    public function getUpazilas(string $district): array
    {
        $normalized = strtolower(trim($district));

        return match ($normalized) {
            'dhaka' => ['Dhanmondi', 'Gulshan', 'Mirpur', 'Uttara', 'Motijheel', 'Savar', 'Keraniganj'],
            'gazipur' => ['Sreepur', 'Kaliakair', 'Kapasia', 'Gazipur Sadar'],
            'chattogram' => ['Double Mooring', 'Panchlaish', 'Hathazari', 'Raozan'],
            default => ['Sadar'],
        };
    }

    /**
     * Validate the division-district-upazila combination.
     */
    public function validateGeo(string $division, string $district, string $upazila): bool
    {
        $divisionEnum = Division::tryFrom($division);
        if (! $divisionEnum) {
            return false;
        }

        $districts = $this->getDistricts($divisionEnum);
        if (! in_array($district, $districts)) {
            return false;
        }

        $upazilas = $this->getUpazilas($district);

        return in_array($upazila, $upazilas) || in_array('Sadar', $upazilas);
    }
}
