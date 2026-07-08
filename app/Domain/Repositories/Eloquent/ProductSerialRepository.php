<?php

declare(strict_types=1);

namespace App\Domain\Repositories\Eloquent;

use App\Domain\Repositories\ProductSerialRepositoryInterface;
use Webkul\Core\Eloquent\Repository;

class ProductSerialRepository extends Repository implements ProductSerialRepositoryInterface
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'App\Domain\Models\ProductSerial';
    }
}
