<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\ValueObjects\Money;

final class PriceCalculator
{
    /**
     * Calculate total price including VAT.
     */
    public function calculateTotal(Money $subtotal, float $vatRate = 0.05): Money
    {
        $vat = $subtotal->multiply($vatRate);

        return $subtotal->add($vat);
    }
}
