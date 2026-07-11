<?php

declare(strict_types=1);

namespace App\Domain\Rules;

use App\Domain\Contracts\CompatibilityRuleInterface;
use App\Domain\Responses\CompatibilityResult;

final readonly class ECCCompatibilityRule implements CompatibilityRuleInterface
{
    public function evaluate(array $profiles): CompatibilityResult
    {
        $ram = $profiles['ram'] ?? null;
        $cpu = $profiles['cpu'] ?? null;
        $motherboard = $profiles['motherboard'] ?? null;

        if (! $ram) {
            return CompatibilityResult::compatible();
        }

        $ramIsEcc = $ram->get('ram_is_ecc');

        if ($ramIsEcc) {
            $cpuEcc = $cpu ? $cpu->get('cpu_supports_ecc') : null;
            $mbEcc = $motherboard ? $motherboard->get('motherboard_supports_ecc') : null;

            if ($cpu && ! $cpuEcc) {
                return CompatibilityResult::incompatible(
                    'ECC_NOT_SUPPORTED_BY_CPU',
                    'ECC RAM is selected but the CPU does not support ECC.',
                    'CPU supports_ecc is false.',
                    'The selected processor does not support Error-Correcting Code (ECC) memory.',
                    'Choose non-ECC memory or select a workstation/server CPU that supports ECC.'
                );
            }

            if ($motherboard && ! $mbEcc) {
                return CompatibilityResult::incompatible(
                    'ECC_NOT_SUPPORTED_BY_MOTHERBOARD',
                    'ECC RAM is selected but the Motherboard does not support ECC.',
                    'Motherboard supports_ecc is false.',
                    'The selected motherboard does not support Error-Correcting Code (ECC) memory routing.',
                    'Choose non-ECC memory or select a motherboard that supports ECC.'
                );
            }
        }

        return CompatibilityResult::compatible();
    }
}
