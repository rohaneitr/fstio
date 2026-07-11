<?php

declare(strict_types=1);

namespace App\Domain\Rules;

use App\Domain\Contracts\CompatibilityRuleInterface;
use App\Domain\Responses\CompatibilityResult;

final readonly class ChipsetCompatibilityRule implements CompatibilityRuleInterface
{
    public function evaluate(array $profiles): CompatibilityResult
    {
        $cpu = $profiles['cpu'] ?? null;
        $motherboard = $profiles['motherboard'] ?? null;

        if (! $cpu || ! $motherboard) {
            return CompatibilityResult::compatible();
        }

        $supportedChipsets = $cpu->get('supported_chipsets'); // e.g. "B650,X670,A620"
        $mbChipset = $motherboard->get('motherboard_chipset');

        if ($supportedChipsets && $mbChipset) {
            $chipsets = array_map('trim', explode(',', strtolower((string) $supportedChipsets)));
            if (! in_array(strtolower((string) $mbChipset), $chipsets, true)) {
                return CompatibilityResult::incompatible(
                    'CHIPSET_MISMATCH',
                    'CPU and Motherboard chipsets are incompatible.',
                    "Motherboard chipset is {$mbChipset} but CPU supports: {$supportedChipsets}.",
                    "The selected CPU is not compatible with the motherboard's {$mbChipset} chipset.",
                    "Select a motherboard with one of the supported chipsets: {$supportedChipsets}."
                );
            }
        }

        return CompatibilityResult::compatible();
    }
}
