<?php

declare(strict_types=1);

namespace App\Domain\Rules;

use App\Domain\Contracts\CompatibilityRuleInterface;
use App\Domain\Responses\CompatibilityResult;

final readonly class MemorySlotRule implements CompatibilityRuleInterface
{
    public function evaluate(array $profiles): CompatibilityResult
    {
        $ram = $profiles['ram'] ?? null;
        $motherboard = $profiles['motherboard'] ?? null;

        if (! $ram || ! $motherboard) {
            return CompatibilityResult::compatible();
        }

        $ramModules = $ram->get('ram_modules'); // e.g. 4
        $mbSlots = $motherboard->get('motherboard_ram_slots'); // e.g. 2

        if ($ramModules && $mbSlots && (int) $ramModules > (int) $mbSlots) {
            return CompatibilityResult::incompatible(
                'RAM_SLOTS_EXCEEDED',
                'RAM module count exceeds available motherboard memory slots.',
                "Selected memory has {$ramModules} modules but Motherboard only has {$mbSlots} slots.",
                "You have selected a memory kit containing {$ramModules} sticks, which exceeds the motherboard's physical DIMM slots ({$mbSlots}).",
                "Select a memory kit with {$mbSlots} or fewer modules."
            );
        }

        return CompatibilityResult::compatible();
    }
}
