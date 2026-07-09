<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

use App\Domain\Models\HardwareProfile;
use App\Domain\Responses\CompatibilityResult;

interface CompatibilityRuleInterface
{
    /**
     * Evaluate the compatibility of the loaded hardware profiles.
     *
     * @param  array<string, HardwareProfile>  $profiles
     */
    public function evaluate(array $profiles): CompatibilityResult;
}
