<?php

declare(strict_types=1);

namespace App\Infrastructure\Pricing;

use App\Domain\Contracts\ProductPricingPortInterface;
use Webkul\Customer\Repositories\CustomerGroupRepository;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Product\Repositories\ProductRepository;

final readonly class BagistoProductPricingAdapter implements ProductPricingPortInterface
{
    public function __construct(
        private ProductRepository $productRepository
    ) {}

    public function resolvePrice(
        int $productId,
        int $quantity = 1,
        ?int $channelId = null,
        ?int $customerGroupId = null
    ): float {
        $product = $this->productRepository->with([
            'attribute_values.attribute',
            'price_indices',
            'inventory_indices',
        ])->find($productId);

        if (! $product) {
            throw new \Exception('Product not found.');
        }

        if ($channelId || $customerGroupId) {
            $channel = $channelId ? core()->getChannel($channelId) : core()->getCurrentChannel();

            $customerGroup = null;
            if ($customerGroupId) {
                $customerGroup = app(CustomerGroupRepository::class)->find($customerGroupId);
            }
            if (! $customerGroup) {
                $customerGroup = app(CustomerRepository::class)->getCurrentGroup();
            }

            $indexer = $product->getTypeInstance()->getPriceIndexer()
                ->setChannel($channel)
                ->setCustomerGroup($customerGroup)
                ->setProduct($product);

            return (float) $indexer->getMinimalPrice($quantity);
        }

        return (float) $product->getTypeInstance()->getFinalPrice($quantity);
    }
}
