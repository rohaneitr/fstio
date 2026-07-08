<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

interface DistrictRepositoryInterface
{
    /**
     * Find district by ID.
     */
    public function find(int $id);

    /**
     * Create district.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data);

    /**
     * Delete district.
     */
    public function delete(int $id);
}
