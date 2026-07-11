<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Repositories\DistrictRepositoryInterface;
use Webkul\Core\Eloquent\Repository;

class DistrictRepository extends Repository implements DistrictRepositoryInterface
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'App\Domain\Models\District';
    }
}
