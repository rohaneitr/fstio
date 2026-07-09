<?php

declare(strict_types=1);

namespace App\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class InventoryAdjusted
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public int $productId,
        public int $inventorySourceId,
        public int $previousQty,
        public int $newQty,
        public int $qtyChange,
        public ?int $userId = null
    ) {}
}
