<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

namespace App\Domain\Repositories\Eloquent;

use App\Domain\Repositories\UpazilaRepositoryInterface;
use Webkul\Core\Eloquent\Repository;

class UpazilaRepository extends Repository implements UpazilaRepositoryInterface
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'App\Domain\Models\Upazila';
    }
}
