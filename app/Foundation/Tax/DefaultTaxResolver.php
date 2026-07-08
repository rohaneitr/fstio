<?php

declare(strict_types=1);

namespace App\Foundation\Tax;

use App\Foundation\Money\Money;

final class DefaultTaxResolver implements TaxResolverInterface
{
    /**
     * Resolve VAT/Tax amount (Default: 5% standard VAT rate in BD tech retail).
     */
    public function calculateTax(Money $price, float $rate = 0.05): Money
    {
        return $price->multiply($rate);
    }
}
