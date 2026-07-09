<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use App\Application\DTO\SaveBuildDto;
use App\Application\Responses\CommandResponse;
use App\Domain\Models\HardwareProfile;
use App\Domain\Repositories\BuildRepositoryInterface;
use App\Domain\Services\CompatibilityEngine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Webkul\Product\Repositories\ProductRepository;

final readonly class SaveBuildHandler
{
    public function __construct(
        private BuildRepositoryInterface $buildRepository,
        private ProductRepository $productRepository,
        private CompatibilityEngine $engine
    ) {}

    /**
     * Handle saving a PC Build.
     */
    public function handle(SaveBuildDto $dto): CommandResponse
    {
        return DB::transaction(function () use ($dto) {
            $build = null;

            if ($dto->buildId) {
                $build = $this->buildRepository->find($dto->buildId);
                if (! $build) {
                    return CommandResponse::failure('PC Build not found.');
                }

                // Optimistic concurrency check
                if ((int) $build->version !== $dto->version) {
                    throw new \Exception('Version mismatch. The build has been modified by another request.');
                }
            }

            // Load products once with EAV attributes
            $productIds = array_values($dto->items);
            $products = $this->productRepository->with([
                'attribute_values.attribute',
            ])->findWhereIn('id', $productIds);

            // Compute total price and power wattage
            $totalPrice = 0;
            $estimatedWattage = 0;
            $keyedProfiles = [];

            foreach ($dto->items as $slot => $productId) {
                $product = $products->firstWhere('id', $productId);
                if ($product) {
                    $profile = HardwareProfile::fromProduct($product);
                    $totalPrice += (float) $profile->get('price', 0);
                    $estimatedWattage += (int) $profile->get('power_draw', 0);
                    $keyedProfiles[$slot] = $profile;
                }
            }

            // Run Compatibility validation
            $compatResults = $this->engine->check($keyedProfiles);
            $validationState = 'compatible';
            foreach ($compatResults as $res) {
                if ($res->getStatus() === 'incompatible') {
                    $validationState = 'incompatible';
                }
            }

            $buildHash = md5(json_encode($dto->items));

            if ($build) {
                $build->update([
                    'total_price' => $totalPrice,
                    'estimated_wattage' => $estimatedWattage,
                    'version' => $build->version + 1,
                    'build_hash' => $buildHash,
                ]);

                // Sync items
                $build->items()->delete();
            } else {
                $build = $this->buildRepository->create([
                    'uuid' => (string) Str::uuid(),
                    'user_id' => $dto->userId,
                    'total_price' => $totalPrice,
                    'estimated_wattage' => $estimatedWattage,
                    'version' => 1,
                    'build_hash' => $buildHash,
                ]);
            }

            foreach ($dto->items as $slot => $productId) {
                $build->items()->create([
                    'product_id' => $productId,
                    'component_type' => $slot,
                ]);
            }

            return CommandResponse::success([
                'id' => $build->id,
                'uuid' => $build->uuid,
                'version' => $build->version,
                'validation_state' => $validationState,
                'total_price' => $totalPrice,
                'estimated_wattage' => $estimatedWattage,
            ], 'PC Build saved successfully.');
        });
    }
}
