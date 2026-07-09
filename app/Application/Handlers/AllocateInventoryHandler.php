<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use App\Application\DTO\AllocateInventoryDto;
use App\Application\Responses\CommandResponse;
use App\Domain\Repositories\ProductInventoryRepositoryInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Webkul\Product\Helpers\Indexers\Inventory as InventoryIndexer;
use Webkul\Product\Repositories\ProductRepository;

final readonly class AllocateInventoryHandler
{
    public function __construct(
        private ProductInventoryRepositoryInterface $inventoryRepository,
        private ProductRepository $productRepository,
        private InventoryIndexer $inventoryIndexer
    ) {}

    /**
     * Handle the allocate inventory command.
     */
    public function handle(AllocateInventoryDto $dto): CommandResponse
    {
        $retries = 3;
        $attempt = 0;

        while (true) {
            $attempt++;
            try {
                DB::transaction(function () use ($dto) {
                    $this->inventoryRepository->reserveQuantity(
                        $dto->productId,
                        $dto->channelId,
                        $dto->reservedQty
                    );
                });

                // Trigger indexing updates post-commit
                $product = $this->productRepository->find($dto->productId);
                if ($product) {
                    $this->inventoryIndexer->setProduct($product)->reindexBatch([$product]);
                }

                return CommandResponse::success([], 'Inventory allocated successfully.');
            } catch (QueryException $e) {
                $isDeadlock = $e->getCode() === '40001' || str_contains($e->getMessage(), 'Deadlock');
                if ($attempt < $retries && $isDeadlock) {
                    usleep(100000);

                    continue;
                }
                throw $e;
            }
        }
    }
}
