<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class ProductCode
{
    public function __construct(private string $sku)
    {
        if (! $this->isValid($sku)) {
            throw new InvalidArgumentException('Product code SKU must be alphanumeric, between 3 and 64 characters.');
        }
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    private function isValid(string $sku): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9\-\_]{3,64}$/', $sku);
    }
}
