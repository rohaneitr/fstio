<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

interface ProductPricingPortInterface
{
    /**
     * Resolve the base unit float price for a product given quantity and context.
     *
     * @throws \Exception When product is not found
     */
    public function resolvePrice(
        int $productId,
        int $quantity = 1,
        ?int $channelId = null,
        ?int $customerGroupId = null
    ): float;
}
