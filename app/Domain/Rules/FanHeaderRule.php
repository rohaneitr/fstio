<?php

declare(strict_types=1);

namespace App\Domain\Rules;

use App\Domain\Contracts\CompatibilityRuleInterface;
use App\Domain\Responses\CompatibilityResult;

final readonly class FanHeaderRule implements CompatibilityRuleInterface
{
    public function evaluate(array $profiles): CompatibilityResult
    {
        $motherboard = $profiles['motherboard'] ?? null;

        if (! $motherboard) {
            return CompatibilityResult::compatible();
        }

        $mbFans = $motherboard->get('motherboard_fan_headers');
        if ($mbFans === null) {
            return CompatibilityResult::compatible();
        }

        $totalFans = 0;
        foreach ($profiles as $key => $profile) {
            if ($key === 'motherboard') {
                continue;
            }

            $fanCount = $profile->get('fan_count');
            if ($fanCount) {
                $totalFans += (int) $fanCount;
            }
        }

        if ($totalFans > (int) $mbFans) {
            return CompatibilityResult::warning(
                'INSUFFICIENT_FAN_HEADERS',
                'PC fan count exceeds available motherboard onboard fan headers.',
                "Selected components include {$totalFans} fans but Motherboard only has {$mbFans} headers.",
                "Your system includes a total of {$totalFans} fans, which is more than the onboard chassis headers supported by the motherboard ({$mbFans}).",
                'This is compatible, but you will need to buy a fan splitter cable or a fan controller hub to connect all fans.'
            );
        }

        return CompatibilityResult::compatible();
    }
}
