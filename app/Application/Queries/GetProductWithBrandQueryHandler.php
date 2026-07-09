<?php

declare(strict_types=1);

namespace App\Application\Queries;

use Webkul\Product\Models\ProductProxy;

final readonly class GetProductWithBrandQueryHandler
{
    /**
     * Handle retrieval of a product with its dynamically mapped brand loaded.
     */
    public function handle(GetProductWithBrandQuery $query)
    {
        return ProductProxy::with('brand')->find($query->getProductId());
    }
}
