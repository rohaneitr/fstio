<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

interface ProductInventoryPortInterface
{
    /**
     * Get physical total inventory sum for a product.
     */
    public function getTotalStock(int $productId): int;
}
