<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Repositories\ProductInventoryRepositoryInterface;
use Webkul\Product\Models\ProductOrderedInventory;

class SalableInventoryEngine
{
    public function __construct(
        private ProductInventoryRepositoryInterface $inventoryRepository
    ) {}

    /**
     * Check if product is salable for the requested quantity on a given channel.
     */
    public function isSalable(int $productId, int $channelId, int $requestQty): bool
    {
        // 1. Calculate active inventory total across active warehouses for the channel
        $record = $this->inventoryRepository->find([
            'product_id' => $productId,
        ]);

        $totalQty = 0;
        foreach ($record as $item) {
            $totalQty += (int) $item->qty;
        }

        // 2. Subtract ordered quantities (reservations)
        $ordered = ProductOrderedInventory::where([
            'product_id' => $productId,
            'channel_id' => $channelId,
        ])->first();

        $orderedQty = $ordered ? (int) $ordered->qty : 0;

        $salableQty = $totalQty - $orderedQty;

        return $salableQty >= $requestQty;
    }
}
