<?php

declare(strict_types=1);

namespace App\Domain\Rules;

use App\Domain\Contracts\CompatibilityRuleInterface;
use App\Domain\Responses\CompatibilityResult;

final readonly class MemoryCapacityRule implements CompatibilityRuleInterface
{
    public function evaluate(array $profiles): CompatibilityResult
    {
        $ram = $profiles['ram'] ?? null;
        $motherboard = $profiles['motherboard'] ?? null;

        if (! $ram || ! $motherboard) {
            return CompatibilityResult::compatible();
        }

        $ramCapacity = $ram->get('ram_capacity'); // e.g. 64
        $mbMaxCapacity = $motherboard->get('motherboard_max_ram_capacity'); // e.g. 128

        if ($ramCapacity && $mbMaxCapacity && (float) $ramCapacity > (float) $mbMaxCapacity) {
            return CompatibilityResult::incompatible(
                'RAM_CAPACITY_EXCEEDED',
                'Selected RAM capacity exceeds the motherboard maximum limit.',
                "Selected RAM capacity is {$ramCapacity}GB but Motherboard maximum is {$mbMaxCapacity}GB.",
                "The total capacity of the selected memory ({$ramCapacity}GB) exceeds the maximum capacity supported by the motherboard ({$mbMaxCapacity}GB).",
                "Choose a memory kit with total capacity equal to or less than {$mbMaxCapacity}GB."
            );
        }

        return CompatibilityResult::compatible();
    }
}
