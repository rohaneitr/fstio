<?php

declare(strict_types=1);

namespace App\Infrastructure\Pricing;

use App\Domain\Contracts\ProductInventoryPortInterface;
use Illuminate\Support\Facades\DB;

final readonly class BagistoProductInventoryAdapter implements ProductInventoryPortInterface
{
    public function getTotalStock(int $productId): int
    {
        return (int) DB::table('product_inventories')
            ->where('product_id', $productId)
            ->sum('qty');
    }
}
