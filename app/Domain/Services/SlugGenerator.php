<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\ValueObjects\Slug;
use Illuminate\Support\Str;

final class SlugGenerator
{
    /**
     * Generate a Slug Value Object.
     */
    public function generate(string $name): Slug
    {
        $slugString = Str::slug($name);

        return new Slug($slugString);
    }
}
