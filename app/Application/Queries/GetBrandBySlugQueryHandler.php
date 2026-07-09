<?php

declare(strict_types=1);

namespace App\Application\Queries;

use App\Domain\Repositories\BrandRepositoryInterface;

final readonly class GetBrandBySlugQueryHandler
{
    public function __construct(private BrandRepositoryInterface $repository) {}

    /**
     * Handle the brand detail query by slug.
     */
    public function handle(GetBrandBySlugQuery $query)
    {
        return $this->repository->findBySlug($query->getSlug());
    }
}
