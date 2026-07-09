<?php

declare(strict_types=1);

namespace App\Domain\Rules;

use App\Domain\Contracts\CompatibilityRuleInterface;
use App\Domain\Responses\CompatibilityResult;

final readonly class CoolerHeightRule implements CompatibilityRuleInterface
{
    public function evaluate(array $profiles): CompatibilityResult
    {
        $cooler = $profiles['cooler'] ?? null;
        $case = $profiles['case'] ?? null;

        if (! $cooler || ! $case) {
            return CompatibilityResult::compatible();
        }

        $coolerHeight = $cooler->get('cooler_height');
        $caseClearance = $case->get('case_cooler_clearance');

        if ($coolerHeight && $caseClearance && (float) $coolerHeight > (float) $caseClearance) {
            return CompatibilityResult::incompatible(
                'COOLER_TOO_TALL',
                'CPU cooler is too tall for the selected PC case.',
                "Cooler height is {$coolerHeight}mm and Case clearance is {$caseClearance}mm.",
                "The selected CPU cooler is {$coolerHeight}mm tall, which exceeds the case's physical space of {$caseClearance}mm.",
                'Choose a lower-profile CPU cooler or a wider PC case.'
            );
        }

        return CompatibilityResult::compatible();
    }
}
