<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class AdjustInventoryQtyDto
{
    public function __construct(
        public int $productId,
        public int $inventorySourceId,
        public int $qtyChange,
        public ?int $userId = null
    ) {}
}
