<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Support\Facades\Cache;

final class RecalculateProductPriceListener
{
    /**
     * Handle product update or order save events to clear product and builder price caches.
     */
    public function handle(mixed $eventData): void
    {
        $productId = null;

        if (is_object($eventData)) {
            $productId = $eventData->id ?? $eventData->product_id ?? null;
        } elseif (is_numeric($eventData)) {
            $productId = (int) $eventData;
        } elseif (is_array($eventData)) {
            $productId = $eventData['id'] ?? $eventData['product_id'] ?? null;
        }

        if ($productId) {
            Cache::forget("product_price_{$productId}");
        }

        // Flush PC builder cache tags safely
        try {
            Cache::tags(['pc_builder', 'commerce_intelligence'])->flush();
        } catch (\BadMethodCallException) {
            // Ignore if cache driver does not support tags
        }
    }
}
