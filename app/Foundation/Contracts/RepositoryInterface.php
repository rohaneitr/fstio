<?php

declare(strict_types=1);

namespace App\Foundation\Contracts;

interface RepositoryInterface
{
    /**
     * Find a resource by ID.
     */
    public function find(int $id): ?object;

    /**
     * Find all resources.
     *
     * @return array<int, object>
     */
    public function all(): array;
}
