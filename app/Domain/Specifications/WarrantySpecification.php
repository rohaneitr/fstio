<?php

declare(strict_types=1);

namespace App\Domain\Specifications;

use App\Domain\ValueObjects\Warranty;

final class WarrantySpecification
{
    /**
     * Satisfied if the warranty duration matches retail guidelines.
     */
    public function isSatisfiedBy(Warranty $warranty): bool
    {
        // For custom validation parameters (e.g. lifetime is mapped to 120 months)
        return $warranty->getMonths() >= 0 && $warranty->getMonths() <= 120;
    }
}
