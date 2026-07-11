<?php

declare(strict_types=1);

namespace App\Domain\Rules;

use App\Domain\Contracts\CompatibilityRuleInterface;
use App\Domain\Responses\CompatibilityResult;

final readonly class PSUConnectorRule implements CompatibilityRuleInterface
{
    public function evaluate(array $profiles): CompatibilityResult
    {
        $gpu = $profiles['gpu'] ?? null;
        $psu = $profiles['psu'] ?? null;

        if (! $gpu || ! $psu) {
            return CompatibilityResult::compatible();
        }

        $gpuConnectors = $gpu->get('gpu_pcie_connectors_required');
        $psuConnectors = $psu->get('psu_pcie_connectors_available');

        if ($gpuConnectors && $psuConnectors && (int) $gpuConnectors > (int) $psuConnectors) {
            return CompatibilityResult::incompatible(
                'INSUFFICIENT_PSU_CONNECTORS',
                'PSU does not provide enough PCIe connectors to power the selected GPU.',
                "GPU requires {$gpuConnectors} connectors but PSU only provides {$psuConnectors}.",
                "The selected power supply only provides {$psuConnectors} PCIe power cables, while the graphics card requires {$gpuConnectors} separate connections.",
                'Choose a power supply with more PCIe connectors or a GPU that requires less power.'
            );
        }

        $gpuRequires12vhpwr = $gpu->get('gpu_requires_12vhpwr');
        $psuHas12vhpwr = $psu->get('psu_has_12vhpwr');

        if ($gpuRequires12vhpwr && ! $psuHas12vhpwr) {
            return CompatibilityResult::warning(
                'MISSING_12VHPWR_CONNECTOR',
                'PSU lacks the native 12VHPWR connector required by the GPU.',
                'GPU requires native 12VHPWR cable, but PSU lacks native support.',
                'The selected graphics card uses the new 12VHPWR PCIe Gen 5 standard, but the selected power supply does not have a native cable. You will need to use a multi-8-pin adapter.',
                'This is compatible via adapter, but consider selecting an ATX 3.0 power supply with a native 12VHPWR connection.'
            );
        }

        return CompatibilityResult::compatible();
    }
}
