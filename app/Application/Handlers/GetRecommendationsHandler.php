<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use App\Application\DTO\GetRecommendationsDto;
use App\Application\Responses\CommandResponse;
use App\Domain\Services\HybridRecommendationPipeline;
use Illuminate\Support\Facades\Cache;
use Webkul\Product\Repositories\ProductRepository;

final readonly class GetRecommendationsHandler
{
    public function __construct(
        private ProductRepository $productRepository,
        private HybridRecommendationPipeline $pipeline
    ) {}

    /**
     * Handle recommendation request.
     */
    public function handle(GetRecommendationsDto $dto): CommandResponse
    {
        $cacheKey = "recommendations_{$dto->productId}_{$dto->strategy}";

        $modelClass = get_class(app($this->productRepository->model()));

        $payload = Cache::tags(['recommendations', 'product_scores'])->remember($cacheKey, 86400, function () use ($dto, $modelClass) {
            $targetProduct = $modelClass::with([
                'attribute_values.attribute',
            ])->find($dto->productId);

            if (! $targetProduct) {
                return null;
            }

            // Load candidate alternative products in a single database query to prevent N+1 loops
            $candidates = $modelClass::with([
                'attribute_values.attribute',
            ])->latest('id')->limit(50)->get();

            $suggestions = $this->pipeline->suggestAlternatives(
                $targetProduct,
                $candidates->all(),
                $dto->strategy
            );

            // Map suggestions to serializable arrays
            return array_map(fn ($s) => [
                'product_id' => $s['product']->id,
                'name' => $s['product']->sku,
                'price' => $s['price'],
                'scores' => $s['scores'],
                'distance' => round($s['distance'], 4),
            ], $suggestions);
        });

        if ($payload === null) {
            return CommandResponse::failure('Product not found.');
        }

        return CommandResponse::success($payload, 'Recommendations loaded successfully.');
    }
}
