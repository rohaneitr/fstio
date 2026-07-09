<?php

declare(strict_types=1);

namespace App\Domain\Rules;

use App\Domain\Contracts\CompatibilityRuleInterface;
use App\Domain\Responses\CompatibilityResult;

final readonly class GPULengthRule implements CompatibilityRuleInterface
{
    public function evaluate(array $profiles): CompatibilityResult
    {
        $gpu = $profiles['gpu'] ?? null;
        $case = $profiles['case'] ?? null;

        if (! $gpu || ! $case) {
            return CompatibilityResult::compatible();
        }

        $gpuLength = $gpu->get('gpu_length');
        $caseClearance = $case->get('case_gpu_clearance');

        if ($gpuLength && $caseClearance && (float) $gpuLength > (float) $caseClearance) {
            return CompatibilityResult::incompatible(
                'GPU_TOO_LONG',
                'GPU is too long for the selected PC case.',
                "GPU length is {$gpuLength}mm and Case clearance is {$caseClearance}mm.",
                "The selected graphics card is {$gpuLength}mm long, which exceeds the case's physical space of {$caseClearance}mm.",
                'Choose a smaller GPU model or a larger PC case.'
            );
        }

        return CompatibilityResult::compatible();
    }
}
