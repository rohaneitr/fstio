<?php

declare(strict_types=1);

namespace App\Domain\Events;

use Illuminate\Queue\SerializesModels;

class BrandDeleted
{
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public int $brandId) {}
}
