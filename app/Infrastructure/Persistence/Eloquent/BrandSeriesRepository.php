<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Repositories\BrandSeriesRepositoryInterface;
use Webkul\Core\Eloquent\Repository;

class BrandSeriesRepository extends Repository implements BrandSeriesRepositoryInterface
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'App\Domain\Models\BrandSeries';
    }
}
