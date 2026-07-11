<?php

declare(strict_types=1);

namespace App\Domain\Rules;

use App\Domain\Contracts\CompatibilityRuleInterface;
use App\Domain\Responses\CompatibilityResult;

final readonly class StorageInterfaceRule implements CompatibilityRuleInterface
{
    public function evaluate(array $profiles): CompatibilityResult
    {
        $motherboard = $profiles['motherboard'] ?? null;

        if (! $motherboard) {
            return CompatibilityResult::compatible();
        }

        $mbSataPorts = $motherboard->get('motherboard_sata_ports');
        $mbM2Slots = $motherboard->get('motherboard_m2_slots');

        $sataCount = 0;
        $m2Count = 0;

        foreach ($profiles as $key => $profile) {
            if ($key === 'motherboard') {
                continue;
            }

            $storageInterface = $profile->get('storage_interface');
            if ($storageInterface) {
                if (stripos((string) $storageInterface, 'm.2') !== false || stripos((string) $storageInterface, 'nvme') !== false) {
                    $m2Count++;
                } elseif (stripos((string) $storageInterface, 'sata') !== false) {
                    $sataCount++;
                }
            }
        }

        if ($mbSataPorts !== null && $sataCount > (int) $mbSataPorts) {
            return CompatibilityResult::incompatible(
                'INSUFFICIENT_SATA_PORTS',
                'The number of selected SATA drives exceeds available motherboard SATA ports.',
                "Selected {$sataCount} SATA drives but Motherboard only has {$mbSataPorts} ports.",
                "You have added {$sataCount} SATA-based drives, which exceeds the motherboard's physical SATA headers ({$mbSataPorts}).",
                'Select a motherboard with more SATA ports or remove some SATA drives.'
            );
        }

        if ($mbM2Slots !== null && $m2Count > (int) $mbM2Slots) {
            return CompatibilityResult::incompatible(
                'INSUFFICIENT_M2_SLOTS',
                'The number of selected M.2 drives exceeds available motherboard M.2 slots.',
                "Selected {$m2Count} M.2 SSDs but Motherboard only has {$mbM2Slots} slots.",
                "You have added {$m2Count} M.2 form-factor SSDs, which exceeds the motherboard's physical slots ({$mbM2Slots}).",
                'Select a motherboard with more M.2 slots or choose standard 2.5" SATA drives.'
            );
        }

        return CompatibilityResult::compatible();
    }
}
