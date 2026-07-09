<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class AssignBrandToProductDto
{
    public function __construct(
        private int $productId,
        private ?int $brandId
    ) {}

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getBrandId(): ?int
    {
        return $this->brandId;
    }
}
