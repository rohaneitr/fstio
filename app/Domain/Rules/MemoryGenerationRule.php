<?php

declare(strict_types=1);

namespace App\Domain\Rules;

use App\Domain\Contracts\CompatibilityRuleInterface;
use App\Domain\Responses\CompatibilityResult;

final readonly class MemoryGenerationRule implements CompatibilityRuleInterface
{
    public function evaluate(array $profiles): CompatibilityResult
    {
        $ram = $profiles['ram'] ?? null;
        $motherboard = $profiles['motherboard'] ?? null;

        if (! $ram || ! $motherboard) {
            return CompatibilityResult::compatible();
        }

        $ramGen = $ram->get('ram_generation');
        $mbGen = $motherboard->get('motherboard_ram_generation');

        if ($ramGen && $mbGen && strtolower((string) $ramGen) !== strtolower((string) $mbGen)) {
            return CompatibilityResult::incompatible(
                'RAM_GENERATION_MISMATCH',
                'RAM and Motherboard memory generation do not match.',
                "RAM generation is {$ramGen} and Motherboard supports {$mbGen}.",
                "The selected memory is {$ramGen} which is not electrically or physically compatible with the motherboard ({$mbGen} slot).",
                'Select a matching memory kit.'
            );
        }

        return CompatibilityResult::compatible();
    }
}
