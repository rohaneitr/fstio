<?php

declare(strict_types=1);

namespace App\Domain\Specifications;

use App\Domain\Models\Brand;
use App\Domain\ValueObjects\Slug;

final class BrandSpecification
{
    /**
     * Satisfied if the slug does not match standard test values.
     */
    public function isSatisfiedBy(Brand $brand): bool
    {
        try {
            new Slug($brand->slug);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
