<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

interface CompatibilityRepositoryInterface
{
    /**
     * Find compatibility rule by ID.
     */
    public function find(int $id);

    /**
     * Create compatibility rule.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data);

    /**
     * Delete compatibility rule.
     */
    public function delete(int $id);
}
