<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class CheckCompatibilityDto
{
    /**
     * @param  array<string, int>  $productIds
     */
    public function __construct(
        private array $productIds
    ) {}

    /**
     * Get target product IDs map.
     *
     * @return array<string, int>
     */
    public function getProductIds(): array
    {
        return $this->productIds;
    }
}
