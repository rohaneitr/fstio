<?php

declare(strict_types=1);

namespace App\Domain\Models;

final readonly class HardwareProfile
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        private int $productId,
        private array $attributes = []
    ) {}

    /**
     * Create a HardwareProfile from a native Product model.
     */
    public static function fromProduct($product): self
    {
        $attributes = [];

        if (isset($product->attribute_values)) {
            foreach ($product->attribute_values as $val) {
                $code = $val->attribute?->code;
                if ($code) {
                    // Extract value based on type or fallback to non-null value
                    $attributes[$code] = $val->text_value
                        ?? $val->boolean_value
                        ?? $val->integer_value
                        ?? $val->float_value
                        ?? $val->datetime_value
                        ?? $val->date_value
                        ?? $val->json_value;
                }
            }
        }

        // Standard product fields mapping
        $attributes['id'] = $product->id;
        $attributes['sku'] = $product->sku;
        $attributes['type'] = $product->type;

        return new self($product->id, $attributes);
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    /**
     * Get attribute value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * Convert profile to array.
     */
    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'attributes' => $this->attributes,
        ];
    }
}
