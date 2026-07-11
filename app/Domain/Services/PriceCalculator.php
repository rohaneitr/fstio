<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Application\DTO\CalculatePriceDto;
use App\Domain\Contracts\ProductPricingPortInterface;
use App\Domain\ValueObjects\Money;

final readonly class PriceCalculator
{
    public function __construct(
        private ProductPricingPortInterface $pricingPort
    ) {}

    /**
     * Calculate final price for a product considering quantity, customer group, and inventory.
     */
    public function calculate(CalculatePriceDto $dto): Money
    {
        $productId = $dto->productId;
        $qty = $dto->quantity;

        $price = $this->pricingPort->resolvePrice(
            $productId,
            $qty,
            $dto->channelId,
            $dto->customerGroupId
        );

        $money = Money::BDT($price);

        return $money;
    }
}
