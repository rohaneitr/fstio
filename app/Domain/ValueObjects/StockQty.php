<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class StockQty
{
    private int $value;

    /**
     * Create a new StockQty instance.
     */
    public function __construct(int $value, bool $allowBackorder = false)
    {
        if ($value < 0 && ! $allowBackorder) {
            throw new InvalidArgumentException('Stock quantity cannot be negative.');
        }

        $this->value = $value;
    }

    /**
     * Get the quantity value.
     */
    public function getValue(): int
    {
        return $this->value;
    }
}
