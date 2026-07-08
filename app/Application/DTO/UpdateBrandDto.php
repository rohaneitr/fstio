<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class UpdateBrandDto
{
    public function __construct(
        private int $id,
        private ?string $logoLight = null,
        private ?string $logoDark = null,
        private ?string $websiteUrl = null
    ) {}

    public function getId(): int
    {
        return $this->id;
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
