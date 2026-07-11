<?php

declare(strict_types=1);

namespace App\Domain\Rules;

use App\Domain\Contracts\CompatibilityRuleInterface;
use App\Domain\Responses\CompatibilityResult;

final readonly class GPUThicknessRule implements CompatibilityRuleInterface
{
    public function evaluate(array $profiles): CompatibilityResult
    {
        $gpu = $profiles['gpu'] ?? null;
        $case = $profiles['case'] ?? null;

        if (! $gpu || ! $case) {
            return CompatibilityResult::compatible();
        }

        $gpuSlots = $gpu->get('gpu_slots');
        $caseSlots = $case->get('case_expansion_slots');

        if ($gpuSlots && $caseSlots && (float) $gpuSlots > (float) $caseSlots) {
            return CompatibilityResult::incompatible(
                'GPU_TOO_THICK',
                'Selected GPU thickness exceeds PC case expansion slot capacity.',
                "GPU thickness is {$gpuSlots} slots but Case only has {$caseSlots} expansion slots.",
                "The graphics card is {$gpuSlots} slots thick, but the PC case only supports up to {$caseSlots} expansion slots.",
                'Select a slimmer GPU model or a larger PC case.'
            );
        }

        return CompatibilityResult::compatible();
    }
}
