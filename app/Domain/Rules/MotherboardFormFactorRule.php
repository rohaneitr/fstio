<?php

declare(strict_types=1);

namespace App\Domain\Rules;

use App\Domain\Contracts\CompatibilityRuleInterface;
use App\Domain\Responses\CompatibilityResult;

final readonly class MotherboardFormFactorRule implements CompatibilityRuleInterface
{
    public function evaluate(array $profiles): CompatibilityResult
    {
        $motherboard = $profiles['motherboard'] ?? null;
        $case = $profiles['case'] ?? null;

        if (! $motherboard || ! $case) {
            return CompatibilityResult::compatible();
        }

        $mbForm = $motherboard->get('motherboard_form_factor');
        $caseSupportedForms = $case->get('case_supported_form_factors');

        if ($mbForm && $caseSupportedForms) {
            $supportedForms = array_map('trim', explode(',', strtolower((string) $caseSupportedForms)));
            if (! in_array(strtolower((string) $mbForm), $supportedForms, true)) {
                return CompatibilityResult::incompatible(
                    'FORM_FACTOR_MISMATCH',
                    'Motherboard form factor is not supported by the selected PC case.',
                    "Motherboard size is {$mbForm} and Case supports: {$caseSupportedForms}.",
                    "The selected motherboard ({$mbForm}) is physically too large to fit in the selected computer case ({$caseSupportedForms}).",
                    'Select a smaller motherboard or choose a larger computer case.'
                );
            }
        }

        return CompatibilityResult::compatible();
    }
}
