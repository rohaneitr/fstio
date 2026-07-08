<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Models\CompatibilityRule;
use App\Domain\Specifications\CompatibilitySpecification;

final class CompatibilityEngine
{
    public function __construct(private CompatibilitySpecification $specification) {}

    /**
     * Determine if a child product is compatible with a parent product under a specific rule.
     */
    public function checkCompatible(CompatibilityRule $rule): bool
    {
        return $this->specification->isSatisfiedBy($rule);
    }
}
