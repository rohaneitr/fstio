<?php

declare(strict_types=1);

namespace App\Domain\Rules;

use App\Domain\Contracts\CompatibilityRuleInterface;
use App\Domain\Responses\CompatibilityResult;

final readonly class USBHeaderRule implements CompatibilityRuleInterface
{
    public function evaluate(array $profiles): CompatibilityResult
    {
        $case = $profiles['case'] ?? null;
        $motherboard = $profiles['motherboard'] ?? null;

        if (! $case || ! $motherboard) {
            return CompatibilityResult::compatible();
        }

        $caseRequiresUsb3 = $case->get('case_requires_usb3');
        $caseRequiresTypec = $case->get('case_requires_typec');

        $mbUsb3 = $motherboard->get('motherboard_usb3_headers');
        $mbTypec = $motherboard->get('motherboard_typec_headers');

        if ($caseRequiresUsb3 && $mbUsb3 !== null && (int) $mbUsb3 === 0) {
            return CompatibilityResult::warning(
                'MISSING_USB3_HEADER',
                'Motherboard lacks USB 3.0 front-panel headers required by the PC case.',
                'Case requires USB 3.0 headers but Motherboard provides 0.',
                'The selected PC case includes front-panel USB 3.0 ports, but the motherboard has no USB 3.0 headers. Front ports will not operate without an internal adapter.',
                'Use an internal USB 2.0 to 3.0 adapter or select a motherboard with USB 3.0 header support.'
            );
        }

        if ($caseRequiresTypec && $mbTypec !== null && (int) $mbTypec === 0) {
            return CompatibilityResult::warning(
                'MISSING_TYPEC_HEADER',
                'Motherboard lacks Type-C front-panel headers required by the PC case.',
                'Case requires Type-C headers but Motherboard provides 0.',
                'The selected PC case features a front-panel USB Type-C port, but the motherboard has no matching internal Type-C headers.',
                'Choose a motherboard with a Type-C front header or select a case without front Type-C.'
            );
        }

        return CompatibilityResult::compatible();
    }
}
