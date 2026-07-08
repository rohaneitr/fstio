<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Enums\WarrantyType;
use App\Domain\ValueObjects\Warranty;

final class WarrantyPolicy
{
    /**
     * Check if the product's warranty fits standard retail guidelines.
     */
    public function isExtendable(Warranty $warranty): bool
    {
        return $warranty->getType() === WarrantyType::OFFICIAL;
    }
}
