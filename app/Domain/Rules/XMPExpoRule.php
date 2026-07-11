<?php

declare(strict_types=1);

namespace App\Domain\Rules;

use App\Domain\Contracts\CompatibilityRuleInterface;
use App\Domain\Responses\CompatibilityResult;

final readonly class XMPExpoRule implements CompatibilityRuleInterface
{
    public function evaluate(array $profiles): CompatibilityResult
    {
        $ram = $profiles['ram'] ?? null;
        $motherboard = $profiles['motherboard'] ?? null;

        if (! $ram || ! $motherboard) {
            return CompatibilityResult::compatible();
        }

        $ramProfile = $ram->get('ram_profile'); // e.g. "EXPO", "XMP"
        $supportedProfiles = $motherboard->get('supported_ram_profiles'); // Comma-separated e.g. "XMP"

        if ($ramProfile && $supportedProfiles) {
            $mbProfiles = array_map('trim', explode(',', strtolower((string) $supportedProfiles)));
            $profileLower = strtolower((string) $ramProfile);

            if ($profileLower !== 'both' && ! in_array($profileLower, $mbProfiles, true)) {
                return CompatibilityResult::warning(
                    'RAM_PROFILE_MISMATCH',
                    'RAM performance profile ecosystem mismatch.',
                    "RAM uses {$ramProfile} but Motherboard supports: {$supportedProfiles}.",
                    'The selected memory kit uses AMD EXPO / Intel XMP profile which is not natively supported by this motherboard platform.',
                    'The RAM will run at standard JEDEC speeds, but custom profile overclocking might not work out of the box.'
                );
            }
        }

        return CompatibilityResult::compatible();
    }
}
