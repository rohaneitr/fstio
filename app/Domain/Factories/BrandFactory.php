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
    public function create(
        string $name,
        Slug $slug,
        ?string $logoLight = null,
        ?string $logoDark = null,
        ?string $websiteUrl = null,
        bool $status = true,
        ?string $description = null,
        ?string $metaTitle = null,
        ?string $metaKeywords = null,
        ?string $metaDescription = null,
        int $sortOrder = 0
    ): Brand {
        return new Brand([
            'name' => $name,
            'slug' => $slug->getValue(),
            'logo_light' => $logoLight,
            'logo_dark' => $logoDark,
            'website_url' => $websiteUrl,
            'status' => $status,
            'description' => $description,
            'meta_title' => $metaTitle,
            'meta_keywords' => $metaKeywords,
            'meta_description' => $metaDescription,
            'sort_order' => $sortOrder,
        ]);
    }
}
