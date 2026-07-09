<?php

declare(strict_types=1);

namespace App\Application\Queries;

final readonly class GetBrandBySlugQuery
{
    public function __construct(private string $slug) {}

    public function getSlug(): string
    {
        return $this->slug;
    }
}
