<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class DeleteBuildDto
{
    public function __construct(
        public int $buildId
    ) {}
}
