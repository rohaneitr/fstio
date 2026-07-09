<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use App\Application\DTO\AddBuildToCartDto;
use App\Application\Responses\CommandResponse;
use App\Domain\Repositories\BuildRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class AddBuildToCartHandler
{
    public function __construct(
        private BuildRepositoryInterface $buildRepository
    ) {}

    /**
     * Handle converting build items to native cart items.
     */
    public function handle(AddBuildToCartDto $dto): CommandResponse
    {
        return DB::transaction(function () use ($dto) {
            $build = $this->buildRepository->find($dto->buildId);
            if (! $build) {
                return CommandResponse::failure('PC Build not found.');
            }

            foreach ($build->items as $item) {
                // Ensure helper function cart() exists or resolve facade
                if (function_exists('cart')) {
                    cart()->addProduct($item->product, [
                        'product_id' => $item->product_id,
                        'quantity' => 1,
                    ]);
                }
            }

            return CommandResponse::success([], 'PC Build items converted to cart successfully.');
        });
    }
}
