<?php

declare(strict_types=1);

namespace App\Domain\Rules;

use App\Domain\Contracts\CompatibilityRuleInterface;
use App\Domain\Responses\CompatibilityResult;

final readonly class ARGBHeaderRule implements CompatibilityRuleInterface
{
    public function evaluate(array $profiles): CompatibilityResult
    {
        $motherboard = $profiles['motherboard'] ?? null;

        if (! $motherboard) {
            return CompatibilityResult::compatible();
        }

        $mbArgb = $motherboard->get('motherboard_5v_argb_headers');
        $mbRgb = $motherboard->get('motherboard_12v_rgb_headers');

        foreach ($profiles as $key => $profile) {
            if ($key === 'motherboard') {
                continue;
            }

            $rgbType = $profile->get('rgb_type');
            if ($rgbType) {
                if (strtolower((string) $rgbType) === '5v argb' && $mbArgb !== null && (int) $mbArgb === 0) {
                    return CompatibilityResult::warning(
                        'MISSING_ARGB_HEADER',
                        'Motherboard lacks 5V 3-pin ARGB headers required for component lighting control.',
                        'Component requires 5V 3-pin ARGB headers but Motherboard provides 0.',
                        'The selected component uses 5V addressable RGB lighting, but the motherboard does not have a 5V ARGB connector.',
                        'Choose a motherboard with 5V ARGB header support, or buy an external SATA-powered RGB controller hub.'
                    );
                }

                if (strtolower((string) $rgbType) === '12v rgb' && $mbRgb !== null && (int) $mbRgb === 0) {
                    return CompatibilityResult::warning(
                        'MISSING_RGB_HEADER',
                        'Motherboard lacks 12V 4-pin RGB headers required for component lighting control.',
                        'Component requires 12V 4-pin RGB headers but Motherboard provides 0.',
                        'The selected component uses standard 12V analog RGB, but the motherboard lacks 12V RGB connectors.',
                        'Choose a motherboard with 12V RGB headers, or use an external lighting hub.'
                    );
                }
            }
        }

        return CompatibilityResult::compatible();
    }
}
