<?php

declare(strict_types=1);

namespace App\Domain\Factories;

use App\Domain\Enums\CompatibilityType;
use App\Domain\Models\CompatibilityRule;

final class CompatibilityFactory
{
    /**
     * Create a new CompatibilityRule model instance.
     */
    public function create(int $parentProductId, int $childProductId, CompatibilityType $type): CompatibilityRule
    {
        return new CompatibilityRule([
            'parent_product_id' => $parentProductId,
            'child_product_id' => $childProductId,
            'rule_type' => $type->value,
        ]);
    }
}
