<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Repositories\BuildRepositoryInterface;
use Webkul\Core\Eloquent\Repository;

class BuildRepository extends Repository implements BuildRepositoryInterface
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'App\Domain\Models\Build';
    }

    /**
     * Find build by UUID.
     */
    public function findByUuid(string $uuid)
    {
        return $this->model->where('uuid', $uuid)->first();
    }
}
