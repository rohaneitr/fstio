<?php

declare(strict_types=1);

namespace App\Application\Queries;

final readonly class GetProductWithBrandQuery
{
    public function __construct(private int $productId) {}

    public function getProductId(): int
    {
        return $this->productId;
    }
}
