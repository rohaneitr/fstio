<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

interface UpazilaRepositoryInterface
{
    /**
     * Find upazila by ID.
     */
    public function find(int $id);

    /**
     * Create upazila.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data);

    /**
     * Delete upazila.
     */
    public function delete(int $id);
}
