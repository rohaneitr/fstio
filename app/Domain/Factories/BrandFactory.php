<?php

declare(strict_types=1);

namespace App\Domain\Factories;

use App\Domain\Models\Brand;
use App\Domain\ValueObjects\Slug;

final class BrandFactory
{
    /**
     * Create a new Brand model instance.
     */
    public function create(Slug $slug, ?string $logoLight = null, ?string $logoDark = null, ?string $websiteUrl = null): Brand
    {
        return new Brand([
            'slug' => $slug->getValue(),
            'logo_light' => $logoLight,
            'logo_dark' => $logoDark,
            'website_url' => $websiteUrl,
        ]);
    }
}
