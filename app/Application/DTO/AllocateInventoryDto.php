<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class AllocateInventoryDto
{
    public function __construct(
        public int $productId,
        public int $channelId,
        public int $reservedQty
    ) {}
}
