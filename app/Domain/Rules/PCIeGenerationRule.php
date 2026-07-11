<?php

declare(strict_types=1);

namespace App\Domain\Rules;

use App\Domain\Contracts\CompatibilityRuleInterface;
use App\Domain\Responses\CompatibilityResult;

final readonly class PCIeGenerationRule implements CompatibilityRuleInterface
{
    public function evaluate(array $profiles): CompatibilityResult
    {
        $gpu = $profiles['gpu'] ?? null;
        $motherboard = $profiles['motherboard'] ?? null;

        if (! $gpu || ! $motherboard) {
            return CompatibilityResult::compatible();
        }

        $gpuGen = $gpu->get('gpu_pcie_gen');
        $mbGen = $motherboard->get('motherboard_pcie_gen');

        if ($gpuGen && $mbGen && (int) $gpuGen > (int) $mbGen) {
            return CompatibilityResult::warning(
                'PCIE_GEN_DOWNGRADE',
                'GPU PCIe generation is newer than Motherboard and will run at slower speeds.',
                "GPU PCIe gen is PCIe {$gpuGen}.0 but Motherboard slot supports PCIe {$mbGen}.0.",
                "The graphics card supports PCIe {$gpuGen}.0, but the motherboard's slot is capped at PCIe {$mbGen}.0. Performance will downgrade to the motherboard's slot speed.",
                'This is physically compatible, but consider getting a motherboard that matches the GPU generation to maximize performance.'
            );
        }

        return CompatibilityResult::compatible();
    }
}
