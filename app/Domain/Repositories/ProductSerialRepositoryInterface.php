<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

interface ProductSerialRepositoryInterface
{
    /**
     * Find serial by ID.
     */
    public function find(int $id);

    /**
     * Create serial number.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data);

    /**
     * Update serial number.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(array $data, int $id);

    /**
     * Delete serial number.
     */
    public function delete(int $id);
}
