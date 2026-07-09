<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\ValueObjects\Slug;
use Illuminate\Http\UploadedFile;

final readonly class UpdateBrandDto
{
    public function __construct(
        private int $id,
        private string $name,
        private Slug $slug,
        private ?UploadedFile $logoLight = null,
        private ?UploadedFile $logoDark = null,
        private ?string $websiteUrl = null,
        private bool $status = true,
        private ?string $description = null,
        private ?string $metaTitle = null,
        private ?string $metaKeywords = null,
        private ?string $metaDescription = null,
        private int $sortOrder = 0,
        private bool $deleteLogoLight = false,
        private bool $deleteLogoDark = false
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): Slug
    {
        return $this->slug;
    }

    public function getLogoLight(): ?UploadedFile
    {
        return $this->logoLight;
    }

    public function getLogoDark(): ?UploadedFile
    {
        return $this->logoDark;
    }

    public function getWebsiteUrl(): ?string
    {
        return $this->websiteUrl;
    }

    public function getStatus(): bool
    {
        return $this->status;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function getMetaKeywords(): ?string
    {
        return $this->metaKeywords;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function getDeleteLogoLight(): bool
    {
        return $this->deleteLogoLight;
    }

    public function getDeleteLogoDark(): bool
    {
        return $this->deleteLogoDark;
    }
}
