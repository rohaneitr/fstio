<?php

declare(strict_types=1);

namespace App\Infrastructure\Indexers;

use App\Domain\Contracts\ProductInventoryPortInterface;
use Webkul\BookingProduct\Helpers\Indexers\Price\Booking;

class EnterpriseBookingIndexer extends Booking
{
    /**
     * Get product minimal price.
     *
     * @param  int|null  $qty
     * @return float
     */
    public function getMinimalPrice($qty = null)
    {
        $price = parent::getMinimalPrice($qty);

        if ($price > 0) {
            $stockQty = app(ProductInventoryPortInterface::class)->getTotalStock($this->product->id);
            if ($stockQty > 0 && $stockQty <= 5) {
                $price *= 1.10;
            }
        }

        return $price;
    }
}
