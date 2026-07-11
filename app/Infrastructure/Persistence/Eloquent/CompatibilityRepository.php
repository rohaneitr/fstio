<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Repositories\CompatibilityRepositoryInterface;
use Webkul\Core\Eloquent\Repository;

class CompatibilityRepository extends Repository implements CompatibilityRepositoryInterface
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'App\Domain\Models\CompatibilityRule';
    }
}
