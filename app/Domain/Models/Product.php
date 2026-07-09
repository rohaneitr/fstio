<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Webkul\Product\Models\Product as BaseProduct;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends BaseProduct
{
    /**
     * Get the brand associated with the product.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }
}
