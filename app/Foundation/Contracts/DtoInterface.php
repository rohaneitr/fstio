<?php

declare(strict_types=1);

namespace App\Foundation\Contracts;

interface DtoInterface
{
    /**
     * Convert the DTO to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
