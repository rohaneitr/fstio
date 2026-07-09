<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class DeleteBrandDto
{
    public function __construct(private int $id) {}

    public function getId(): int
    {
        return $this->id;
    }
}
