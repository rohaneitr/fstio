<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

interface BrandRepositoryInterface
{
    /**
     * Find brand by ID.
     */
    public function find(int $id);

    /**
     * Find brand by slug.
     */
    public function findBySlug(string $slug);

    /**
     * Create brand.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data);

    /**
     * Update brand.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(array $data, int $id);

    /**
     * Delete brand.
     */
    public function delete(int $id);
}
