<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\ValueObjects\Slug;

final readonly class CreateBrandDto
{
    public function __construct(
        private Slug $slug,
        private ?string $logoLight = null,
        private ?string $logoDark = null,
        private ?string $websiteUrl = null
    ) {}

    public function getSlug(): Slug
    {
        return $this->slug;
    }

    public function getLogoLight(): ?string
    {
        return $this->logoLight;
    }

    public function getLogoDark(): ?string
    {
        return $this->logoDark;
    }

    public function getWebsiteUrl(): ?string
    {
        return $this->websiteUrl;
    }
}
