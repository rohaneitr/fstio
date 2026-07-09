<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class AddBuildToCartDto
{
    public function __construct(
        public int $buildId
    ) {}
}
