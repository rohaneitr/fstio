<?php

declare(strict_types=1);

namespace App\Domain\Specifications;

use App\Domain\Models\CompatibilityRule;

final class CompatibilitySpecification
{
    /**
     * Satisfied if the parent product and child product IDs are distinct.
     */
    public function isSatisfiedBy(CompatibilityRule $rule): bool
    {
        return $rule->parent_product_id !== $rule->child_product_id;
    }
}
