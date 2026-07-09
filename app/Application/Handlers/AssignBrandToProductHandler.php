<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use App\Application\Authorization\AuthorizerInterface;
use App\Application\DTO\AssignBrandToProductDto;
use App\Application\Exceptions\ValidationException;
use App\Application\Responses\CommandResponse;
use App\Domain\Repositories\BrandRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Webkul\Product\Repositories\ProductRepository;

final readonly class AssignBrandToProductHandler
{
    public function __construct(
        private ProductRepository $productRepository,
        private BrandRepositoryInterface $brandRepository,
        private AuthorizerInterface $authorizer
    ) {}

    /**
     * Handle brand assignment mapping to a product.
     */
    public function handle(AssignBrandToProductDto $dto): CommandResponse
    {
        $this->authorizer->authorize('assign_brand');

        $product = $this->productRepository->find($dto->getProductId());
        if (! $product) {
            throw new ValidationException('Product not found.');
        }

        if ($dto->getBrandId() !== null) {
            $brand = $this->brandRepository->find($dto->getBrandId());
            if (! $brand) {
                throw new ValidationException('Brand not found.');
            }
        }

        DB::transaction(function () use ($product, $dto) {
            $product->brand_id = $dto->getBrandId();
            $product->save();
        });

        return new CommandResponse(true, [
            'product_id' => $product->id,
            'brand_id' => $product->brand_id,
        ], 'Brand assigned successfully.');
    }
}
