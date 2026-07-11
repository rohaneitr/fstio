<?php

declare(strict_types=1);

namespace App\Domain\Rules;

use App\Domain\Contracts\CompatibilityRuleInterface;
use App\Domain\Responses\CompatibilityResult;

final readonly class PCIeLaneRule implements CompatibilityRuleInterface
{
    public function evaluate(array $profiles): CompatibilityResult
    {
        $motherboard = $profiles['motherboard'] ?? null;

        if (! $motherboard) {
            return CompatibilityResult::compatible();
        }

        $mbLanes = $motherboard->get('motherboard_pcie_lanes');
        if (! $mbLanes) {
            return CompatibilityResult::compatible();
        }

        $totalRequired = 0;
        foreach ($profiles as $key => $profile) {
            if ($key === 'motherboard') {
                continue;
            }
            $lanes = $profile->get('pcie_lanes');
            if ($lanes) {
                $totalRequired += (int) $lanes;
            }
        }

        if ($totalRequired > (int) $mbLanes) {
            return CompatibilityResult::incompatible(
                'PCIE_LANES_EXCEEDED',
                'Total required PCIe lanes exceed available motherboard capacity.',
                "Selected components require {$totalRequired} lanes but Motherboard only has {$mbLanes} lanes.",
                "The total PCIe lanes required by your components ({$totalRequired}) exceeds the motherboard's maximum bandwidth allocation ({$mbLanes}).",
                'Select a motherboard with more PCIe lanes or reduce the number of PCIe expansion cards.'
            );
        }

        return CompatibilityResult::compatible();
    }
}
