<?php

declare(strict_types=1);

namespace App\Domain\Rules;

use App\Domain\Contracts\CompatibilityRuleInterface;
use App\Domain\Responses\CompatibilityResult;

final readonly class MemorySpeedRule implements CompatibilityRuleInterface
{
    public function evaluate(array $profiles): CompatibilityResult
    {
        $ram = $profiles['ram'] ?? null;
        $motherboard = $profiles['motherboard'] ?? null;

        if (! $ram || ! $motherboard) {
            return CompatibilityResult::compatible();
        }

        $ramSpeed = $ram->get('ram_speed'); // e.g. 5600
        $mbMaxSpeed = $motherboard->get('motherboard_max_ram_speed'); // e.g. 5200

        if ($ramSpeed && $mbMaxSpeed && (float) $ramSpeed > (float) $mbMaxSpeed) {
            return CompatibilityResult::warning(
                'RAM_SPEED_DOWNCLOCK',
                'RAM speed exceeds the motherboard maximum supported speed and will downclock.',
                "RAM speed is {$ramSpeed}MHz but Motherboard maximum is {$mbMaxSpeed}MHz.",
                "The selected memory kit ({$ramSpeed}MHz) is faster than the motherboard's maximum support ({$mbMaxSpeed}MHz). It will downclock to {$mbMaxSpeed}MHz.",
                "This is compatible, but you could choose a {$mbMaxSpeed}MHz memory kit to save costs or upgrade the motherboard."
            );
        }

        return CompatibilityResult::compatible();
    }
}
