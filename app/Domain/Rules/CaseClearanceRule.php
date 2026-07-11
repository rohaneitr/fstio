<?php

declare(strict_types=1);

namespace App\Domain\Rules;

use App\Domain\Contracts\CompatibilityRuleInterface;
use App\Domain\Responses\CompatibilityResult;

final readonly class CaseClearanceRule implements CompatibilityRuleInterface
{
    public function evaluate(array $profiles): CompatibilityResult
    {
        $psu = $profiles['psu'] ?? null;
        $case = $profiles['case'] ?? null;

        if (! $psu || ! $case) {
            return CompatibilityResult::compatible();
        }

        $psuLength = $psu->get('psu_length');
        $casePsuClearance = $case->get('case_max_psu_length');

        if ($psuLength && $casePsuClearance && (float) $psuLength > (float) $casePsuClearance) {
            return CompatibilityResult::incompatible(
                'PSU_TOO_LONG',
                'Selected PSU is too long for the selected PC case.',
                "PSU length is {$psuLength}mm but Case max limit is {$casePsuClearance}mm.",
                "The selected power supply ({$psuLength}mm) is longer than the maximum clearance space allocated by the case ({$casePsuClearance}mm).",
                'Select a shorter PSU (e.g., standard ATX or SFX size) or choose a larger PC case.'
            );
        }

        return CompatibilityResult::compatible();
    }
}
