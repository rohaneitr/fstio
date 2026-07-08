<?php

declare(strict_types=1);

namespace App\Domain\Repositories\Eloquent;

use App\Domain\Repositories\BrandRepositoryInterface;
use Webkul\Core\Eloquent\Repository;

class BrandRepository extends Repository implements BrandRepositoryInterface
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'App\Domain\Models\Brand';
    }

    /**
     * Find brand by slug.
     */
    public function findBySlug(string $slug)
    {
        return $this->model->where('slug', $slug)->first();
    }
}
