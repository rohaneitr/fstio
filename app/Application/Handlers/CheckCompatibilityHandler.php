<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use App\Application\DTO\CheckCompatibilityDto;
use App\Application\Responses\CommandResponse;
use App\Domain\Models\HardwareProfile;
use App\Domain\Services\CompatibilityEngine;
use Webkul\Product\Repositories\ProductRepository;

final readonly class CheckCompatibilityHandler
{
    public function __construct(
        private ProductRepository $productRepository,
        private CompatibilityEngine $engine
    ) {}

    /**
     * Handle check compatibility command.
     */
    public function handle(CheckCompatibilityDto $dto): CommandResponse
    {
        $ids = array_values($dto->getProductIds());

        // Load all products with EAV values in a single query
        $products = $this->productRepository->with([
            'attribute_values.attribute',
        ])->findWhereIn('id', $ids);

        // Map native products to immutable HardwareProfiles
        $profiles = [];
        foreach ($dto->getProductIds() as $key => $id) {
            $product = $products->firstWhere('id', $id);
            if ($product) {
                $profiles[$key] = HardwareProfile::fromProduct($product);
            }
        }

        // Evaluate compatibility
        $results = $this->engine->check($profiles);

        // Map output responses
        $mapped = array_map(fn ($res) => $res->toArray(), $results);

        return CommandResponse::success($mapped, 'Compatibility checked successfully.');
    }
}
