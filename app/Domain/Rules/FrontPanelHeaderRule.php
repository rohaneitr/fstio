<?php

declare(strict_types=1);

namespace App\Domain\Rules;

use App\Domain\Contracts\CompatibilityRuleInterface;
use App\Domain\Responses\CompatibilityResult;

final readonly class FrontPanelHeaderRule implements CompatibilityRuleInterface
{
    public function evaluate(array $profiles): CompatibilityResult
    {
        $case = $profiles['case'] ?? null;
        $motherboard = $profiles['motherboard'] ?? null;

        if (! $case || ! $motherboard) {
            return CompatibilityResult::compatible();
        }

        $caseAudio = $case->get('case_audio_type');
        $mbAudio = $motherboard->get('motherboard_audio_headers');

        if ($caseAudio && $mbAudio && strtolower((string) $caseAudio) === 'ac97' && strtolower((string) $mbAudio) === 'hd audio') {
            return CompatibilityResult::warning(
                'AUDIO_HEADER_MISMATCH',
                'PC Case uses older AC97 front-panel audio which is incompatible with motherboard HD Audio headers.',
                "Case audio is {$caseAudio} and Motherboard supports {$mbAudio}.",
                'The PC case front panel utilizes legacy AC97 audio connectors, while the motherboard only has modern HD Audio pin layouts. Plugging it in might cause audio noise or missing channels.',
                'Leave the front-panel audio disconnected or use a modern PC case supporting HD Audio.'
            );
        }

        return CompatibilityResult::compatible();
    }
}
