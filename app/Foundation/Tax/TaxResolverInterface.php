<?php

declare(strict_types=1);

namespace App\Foundation\Tax;

use App\Foundation\Money\Money;

interface TaxResolverInterface
{
    /**
     * Resolve VAT/Tax amount for a given base price.
     */
    public function calculateTax(Money $price, float $rate = 0.05): Money;
}
