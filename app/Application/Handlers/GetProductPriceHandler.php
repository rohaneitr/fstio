<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use App\Application\Queries\GetProductPriceQuery;
use App\Domain\Services\PriceCalculator;
use App\Domain\ValueObjects\Money;

final class GetProductPriceHandler
{
    private PriceCalculator $priceCalculator;

    public function __construct(PriceCalculator $priceCalculator)
    {
        $this->priceCalculator = $priceCalculator;
    }

    public function handle(GetProductPriceQuery $query): Money
    {
        return $this->priceCalculator->calculate($query->getDto());
    }
}
