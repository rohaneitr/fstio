<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Product\Models\Product as BaseProduct;
use Webkul\Product\Models\ProductFlat;

class Product extends BaseProduct
{
    /**
     * Get the brand associated with the product.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    /**
     * Dynamic Scarcity Markup: Intercept price attribute access.
     */
    public function getPriceAttribute($value)
    {
        $actualPrice = (float) $value;

        if (empty($actualPrice)) {
            // First fallback: Check price indices
            $index = $this->price_indices->first();
            if ($index) {
                $actualPrice = (float) $index->min_price;
            } else {
                // Second fallback: Check flat table
                $flat = ProductFlat::where('product_id', $this->id)->first();
                $actualPrice = (float) ($flat?->price ?? 0.0);
            }
        }

        return $actualPrice;
    }

    /**
     * Dynamic Scarcity Markup: Intercept price indices relation access.
     */
    public function getPriceIndicesAttribute()
    {
        if (! $this->relationLoaded('price_indices')) {
            $this->setRelation('price_indices', $this->price_indices()->get());
        }

        return $this->getRelationValue('price_indices');
    }
}
