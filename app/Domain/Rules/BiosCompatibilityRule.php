<?php

declare(strict_types=1);

namespace App\Domain\Rules;

use App\Domain\Contracts\CompatibilityRuleInterface;
use App\Domain\Responses\CompatibilityResult;

final readonly class BiosCompatibilityRule implements CompatibilityRuleInterface
{
    public function evaluate(array $profiles): CompatibilityResult
    {
        $cpu = $profiles['cpu'] ?? null;
        $motherboard = $profiles['motherboard'] ?? null;

        if (! $cpu || ! $motherboard) {
            return CompatibilityResult::compatible();
        }

        $requiredBios = $cpu->get('cpu_required_bios'); // e.g. "F10"
        $mbBios = $motherboard->get('motherboard_bios_version'); // e.g. "F2"

        if ($requiredBios && $mbBios) {
            if (version_compare((string) $mbBios, (string) $requiredBios, '<')) {
                return CompatibilityResult::warning(
                    'BIOS_UPDATE_REQUIRED',
                    'A BIOS update may be required for the motherboard to support the CPU.',
                    "Motherboard BIOS is version {$mbBios} but CPU requires {$requiredBios} or higher.",
                    "The motherboard's current BIOS version ({$mbBios}) might not support this CPU out of the box.",
                    "Update the motherboard BIOS to version {$requiredBios} or newer before installing the CPU."
                );
            }
        }

        return CompatibilityResult::compatible();
    }
}
