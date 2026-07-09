<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class CloneBuildDto
{
    public function __construct(
        public int $buildId,
        public ?int $userId = null
    ) {}
}
