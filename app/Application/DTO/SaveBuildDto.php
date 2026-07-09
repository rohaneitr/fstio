<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class SaveBuildDto
{
    /**
     * @param  array<string, int>  $items
     */
    public function __construct(
        public ?int $buildId,
        public ?int $userId,
        public array $items,
        public int $version = 1
    ) {}
}
