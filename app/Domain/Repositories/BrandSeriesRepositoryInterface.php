<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

interface BrandSeriesRepositoryInterface
{
    /**
     * Find series by ID.
     */
    public function find(int $id);

    /**
     * Create series.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data);

    /**
     * Update series.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(array $data, int $id);

    /**
     * Delete series.
     */
    public function delete(int $id);
}
