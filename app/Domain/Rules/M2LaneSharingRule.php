<?php

declare(strict_types=1);

namespace App\Domain\Rules;

use App\Domain\Contracts\CompatibilityRuleInterface;
use App\Domain\Responses\CompatibilityResult;

final readonly class M2LaneSharingRule implements CompatibilityRuleInterface
{
    public function evaluate(array $profiles): CompatibilityResult
    {
        $motherboard = $profiles['motherboard'] ?? null;

        if (! $motherboard) {
            return CompatibilityResult::compatible();
        }

        $sharesLanes = $motherboard->get('motherboard_shares_lanes');

        if ($sharesLanes) {
            $hasM2 = false;
            $hasSata = false;

            foreach ($profiles as $key => $profile) {
                if ($key === 'motherboard') {
                    continue;
                }

                $storageInterface = $profile->get('storage_interface');
                if ($storageInterface) {
                    if (stripos((string) $storageInterface, 'm.2') !== false || stripos((string) $storageInterface, 'nvme') !== false) {
                        $hasM2 = true;
                    } elseif (stripos((string) $storageInterface, 'sata') !== false) {
                        $hasSata = true;
                    }
                }
            }

            if ($hasM2 && $hasSata) {
                return CompatibilityResult::warning(
                    'M2_LANE_SHARING_ACTIVE',
                    'M.2 slot shares PCIe lanes with SATA ports; populating both will disable some SATA ports.',
                    'Motherboard shares_lanes is active when both M.2 and SATA drives are connected.',
                    'The motherboard shares PCIe lanes between the M.2 slot and SATA ports. Populating both drives means certain SATA ports will be disabled.',
                    'Check your motherboard manual for the exact disabled SATA port mapping to avoid boot detection issues.'
                );
            }
        }

        return CompatibilityResult::compatible();
    }
}
