<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Contracts\CompatibilityRuleInterface;
use App\Domain\Models\CompatibilityRule;
use App\Domain\Models\HardwareProfile;
use App\Domain\Responses\CompatibilityResult;
use App\Domain\Rules\ARGBHeaderRule;
use App\Domain\Rules\BiosCompatibilityRule;
use App\Domain\Rules\CaseClearanceRule;
use App\Domain\Rules\ChipsetCompatibilityRule;
use App\Domain\Rules\CoolerHeightRule;
use App\Domain\Rules\ECCCompatibilityRule;
use App\Domain\Rules\FanHeaderRule;
use App\Domain\Rules\FrontPanelHeaderRule;
use App\Domain\Rules\GPULengthRule;
use App\Domain\Rules\GPUThicknessRule;
use App\Domain\Rules\M2LaneSharingRule;
use App\Domain\Rules\MemoryCapacityRule;
use App\Domain\Rules\MemoryGenerationRule;
use App\Domain\Rules\MemorySlotRule;
use App\Domain\Rules\MemorySpeedRule;
use App\Domain\Rules\MotherboardFormFactorRule;
use App\Domain\Rules\NVMeGenerationRule;
use App\Domain\Rules\PCIeGenerationRule;
use App\Domain\Rules\PCIeLaneRule;
use App\Domain\Rules\PSUConnectorRule;
use App\Domain\Rules\PSUHeadroomRule;
use App\Domain\Rules\SocketCompatibilityRule;
use App\Domain\Rules\StorageInterfaceRule;
use App\Domain\Rules\USBHeaderRule;
use App\Domain\Rules\XMPExpoRule;
use App\Domain\Specifications\CompatibilitySpecification;

class CompatibilityEngine
{
    private ?CompatibilitySpecification $specification = null;

    public function __construct(?CompatibilitySpecification $specification = null)
    {
        $this->specification = $specification;
    }

    /**
     * @var array<class-string<CompatibilityRuleInterface>>
     */
    private array $ruleClasses = [
        SocketCompatibilityRule::class,
        MemoryGenerationRule::class,
        GPULengthRule::class,
        CoolerHeightRule::class,
        PSUHeadroomRule::class,
        ChipsetCompatibilityRule::class,
        BiosCompatibilityRule::class,
        MemorySpeedRule::class,
        MemoryCapacityRule::class,
        MemorySlotRule::class,
        ECCCompatibilityRule::class,
        XMPExpoRule::class,
        PCIeGenerationRule::class,
        PCIeLaneRule::class,
        GPUThicknessRule::class,
        CaseClearanceRule::class,
        PSUConnectorRule::class,
        StorageInterfaceRule::class,
        NVMeGenerationRule::class,
        M2LaneSharingRule::class,
        USBHeaderRule::class,
        FrontPanelHeaderRule::class,
        ARGBHeaderRule::class,
        FanHeaderRule::class,
        MotherboardFormFactorRule::class,
    ];

    /**
     * Check compatibility for hardware profiles.
     *
     * @param  array<string, HardwareProfile>  $profiles
     * @return array<CompatibilityResult>
     */
    public function check(array $profiles): array
    {
        $results = [];

        foreach ($this->ruleClasses as $ruleClass) {
            /** @var CompatibilityRuleInterface $rule */
            $rule = new $ruleClass;
            $result = $rule->evaluate($profiles);

            $results[] = $result;

            // Short-circuit execution on Critical / Incompatible failure
            if ($result->getStatus() === 'incompatible') {
                break;
            }
        }

        return $results;
    }

    /**
     * Check if a compatibility rule's invariants are satisfied.
     */
    public function checkCompatible(CompatibilityRule $rule): bool
    {
        $spec = $this->specification ?? new CompatibilitySpecification;

        return $spec->isSatisfiedBy($rule);
    }
}
