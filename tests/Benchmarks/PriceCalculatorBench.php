<?php

declare(strict_types=1);

namespace Tests\Benchmarks;

use App\Domain\ValueObjects\Money;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;

final class PriceCalculatorBench
{
    /**
     * Benchmark single price calculation and overlay multiplication.
     * Target metric: < 5ms (5,000.000 microseconds)
     */
    #[Revs(1000)]
    #[Iterations(5)]
    public function benchSinglePriceCalculation(): void
    {
        $basePrice = Money::BDT(15000);
        $scarcityOverlay = $basePrice->multiply(1.10);
        $discounted = $scarcityOverlay->subtract(Money::BDT(500));
        $discounted->getDecimalAmount();
    }

    /**
     * Benchmark batch calculation of 100 items.
     * Target metric: < 100ms (100,000.000 microseconds)
     */
    #[Revs(100)]
    #[Iterations(5)]
    public function benchBatchPriceCalculation100(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $basePrice = Money::BDT(1000 + $i * 10);
            $overlay = $basePrice->multiply(1.10);
            $overlay->getDecimalAmount();
        }
    }
}
