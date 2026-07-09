<?php

declare(strict_types=1);

namespace App\Domain\Rules;

use App\Domain\Contracts\CompatibilityRuleInterface;
use App\Domain\Responses\CompatibilityResult;

final readonly class PSUHeadroomRule implements CompatibilityRuleInterface
{
    public function evaluate(array $profiles): CompatibilityResult
    {
        $cpu = $profiles['cpu'] ?? null;
        $gpu = $profiles['gpu'] ?? null;
        $psu = $profiles['psu'] ?? null;

        if (! $psu) {
            return CompatibilityResult::compatible();
        }

        $cpuPower = $cpu ? (float) $cpu->get('power_draw', 0) : 0;
        $gpuPower = $gpu ? (float) $gpu->get('power_draw', 0) : 0;
        $psuWattage = (float) $psu->get('psu_wattage', 0);

        $totalDraw = $cpuPower + $gpuPower + 50; // Add 50W motherboard/accessories buffer

        if ($psuWattage && $totalDraw > $psuWattage) {
            return CompatibilityResult::incompatible(
                'INSUFFICIENT_PSU_WATTAGE',
                'PSU wattage is insufficient for the system power requirements.',
                "Estimated power draw is {$totalDraw}W (CPU: {$cpuPower}W, GPU: {$gpuPower}W) and PSU is {$psuWattage}W.",
                "The selected power supply ({$psuWattage}W) does not have enough capacity to safely run the CPU and GPU (estimated {$totalDraw}W draw).",
                'Select a higher-wattage power supply.'
            );
        }

        return CompatibilityResult::compatible();
    }
}
