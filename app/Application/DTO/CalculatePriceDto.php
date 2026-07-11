<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class CalculatePriceDto
{
    public function __construct(
        public int $productId,
        public int $quantity = 1,
        public ?int $customerGroupId = null,
        public ?int $channelId = null
    ) {}
}
