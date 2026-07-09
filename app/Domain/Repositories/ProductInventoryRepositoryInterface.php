<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

interface ProductInventoryRepositoryInterface
{
    /**
     * Find single inventory record for a product and source.
     */
    public function findForProductAndSource(int $productId, int $inventorySourceId);

    /**
     * Adjust physical inventory quantity.
     */
    public function adjustQuantity(int $productId, int $inventorySourceId, int $qtyChange, ?int $userId = null): void;

    /**
     * Reserve salable inventory quantity.
     */
    public function reserveQuantity(int $productId, int $channelId, int $reservedQty): void;
}
