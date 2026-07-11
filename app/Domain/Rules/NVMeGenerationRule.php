<?php

declare(strict_types=1);

namespace App\Domain\Rules;

use App\Domain\Contracts\CompatibilityRuleInterface;
use App\Domain\Responses\CompatibilityResult;

final readonly class NVMeGenerationRule implements CompatibilityRuleInterface
{
    public function evaluate(array $profiles): CompatibilityResult
    {
        $motherboard = $profiles['motherboard'] ?? null;

        if (! $motherboard) {
            return CompatibilityResult::compatible();
        }

        $mbM2Gen = $motherboard->get('motherboard_m2_pcie_gen');

        if ($mbM2Gen) {
            foreach ($profiles as $key => $profile) {
                if ($key === 'motherboard') {
                    continue;
                }

                $storageInterface = $profile->get('storage_interface');
                if ($storageInterface && (stripos((string) $storageInterface, 'm.2') !== false || stripos((string) $storageInterface, 'nvme') !== false)) {
                    $storageGen = $profile->get('storage_pcie_gen');
                    if ($storageGen && (int) $storageGen > (int) $mbM2Gen) {
                        return CompatibilityResult::warning(
                            'NVME_GEN_DOWNGRADE',
                            'Selected NVMe SSD generation exceeds motherboard M.2 slot generation.',
                            "SSD uses PCIe Gen {$storageGen} but Motherboard M.2 slot supports PCIe Gen {$mbM2Gen}.",
                            "The selected NVMe SSD supports PCIe Gen {$storageGen}, but the motherboard's M.2 slot is capped at PCIe Gen {$mbM2Gen}. The SSD will run at down-clocked Gen {$mbM2Gen} speeds.",
                            'This is physically compatible, but consider selecting a Gen-matching motherboard to capture maximum read/write speeds.'
                        );
                    }
                }
            }
        }

        return CompatibilityResult::compatible();
    }
}
