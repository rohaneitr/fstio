<?php

declare(strict_types=1);

namespace App\Application\Queries;

use App\Domain\Services\PriceCalculator;
use App\Domain\ValueObjects\Money;

final readonly class GetProductPriceQueryHandler
{
    public function __construct(
        private PriceCalculator $priceCalculator
    ) {}

    /**
     * Handle the dynamic price calculation query.
     */
    public function handle(GetProductPriceQuery $query): Money
    {
        return $this->priceCalculator->calculate($query->getDto());
    }
}
