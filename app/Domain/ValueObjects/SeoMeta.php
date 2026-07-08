<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

final readonly class SeoMeta
{
    public function __construct(
        private string $title,
        private string $description
    ) {}

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
