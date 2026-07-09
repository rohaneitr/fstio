<?php

declare(strict_types=1);

namespace App\Domain\Events;

use App\Domain\Models\Brand;
use Illuminate\Queue\SerializesModels;

class BrandUpdated
{
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Brand $brand) {}
}
