<?php

declare(strict_types=1);

namespace App\Foundation\Money;

final class MoneyFormatter
{
    /**
     * Format Money Value Object to string.
     */
    public function format(Money $money): string
    {
        $symbol = $money->getCurrency()->getSymbol();

        return $symbol.number_format($money->getDecimalAmount(), 2);
    }
}
