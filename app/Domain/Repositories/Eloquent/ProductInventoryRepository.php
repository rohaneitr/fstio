<?php

declare(strict_types=1);

namespace App\Domain\Repositories\Eloquent;

use App\Domain\Events\InventoryAdjusted;
use App\Domain\Events\InventoryReserved;
use App\Domain\Repositories\ProductInventoryRepositoryInterface;
use App\Domain\ValueObjects\StockQty;
use Illuminate\Support\Facades\DB;
use Webkul\Product\Models\ProductOrderedInventory;
use Webkul\Product\Repositories\ProductInventoryRepository as BaseRepository;

class ProductInventoryRepository extends BaseRepository implements ProductInventoryRepositoryInterface
{
    /**
     * Find single inventory record for a product and source.
     */
    public function findForProductAndSource(int $productId, int $inventorySourceId)
    {
        return $this->model->where([
            'product_id' => $productId,
            'inventory_source_id' => $inventorySourceId,
        ])->first();
    }

    /**
     * Adjust physical inventory quantity.
     */
    public function adjustQuantity(int $productId, int $inventorySourceId, int $qtyChange, ?int $userId = null): void
    {
        $record = $this->model->where([
            'product_id' => $productId,
            'inventory_source_id' => $inventorySourceId,
        ])->lockForUpdate()->first();

        $previousQty = $record ? (int) $record->qty : 0;
        $newQtyValue = $previousQty + $qtyChange;

        // Invariant protection using Value Object
        $stockQty = new StockQty($newQtyValue);

        if ($record) {
            $record->qty = $stockQty->getValue();
            $record->save();
        } else {
            $this->create([
                'product_id' => $productId,
                'inventory_source_id' => $inventorySourceId,
                'qty' => $stockQty->getValue(),
                'vendor_id' => 0,
            ]);
        }

        // Create audit log
        DB::table('inventory_audit_logs')->insert([
            'product_id' => $productId,
            'inventory_source_id' => $inventorySourceId,
            'user_id' => $userId,
            'action' => 'adjustment',
            'previous_qty' => $previousQty,
            'new_qty' => $stockQty->getValue(),
            'qty_change' => $qtyChange,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Publish domain event
        event(new InventoryAdjusted(
            $productId,
            $inventorySourceId,
            $previousQty,
            $stockQty->getValue(),
            $qtyChange,
            $userId
        ));
    }

    /**
     * Reserve salable inventory quantity.
     */
    public function reserveQuantity(int $productId, int $channelId, int $reservedQty): void
    {
        $ordered = ProductOrderedInventory::where([
            'product_id' => $productId,
            'channel_id' => $channelId,
        ])->lockForUpdate()->first();

        if ($ordered) {
            $ordered->qty += $reservedQty;
            $ordered->save();
        } else {
            ProductOrderedInventory::create([
                'product_id' => $productId,
                'channel_id' => $channelId,
                'qty' => $reservedQty,
            ]);
        }

        // Publish domain event
        event(new InventoryReserved(
            $productId,
            $channelId,
            $reservedQty
        ));
    }
}
