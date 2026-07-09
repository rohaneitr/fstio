<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use App\Application\DTO\AdjustInventoryQtyDto;
use App\Application\Responses\CommandResponse;
use App\Domain\Repositories\ProductInventoryRepositoryInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Webkul\Product\Helpers\Indexers\Inventory as InventoryIndexer;
use Webkul\Product\Repositories\ProductRepository;

final readonly class AdjustInventoryQtyHandler
{
    public function __construct(
        private ProductInventoryRepositoryInterface $inventoryRepository,
        private ProductRepository $productRepository,
        private InventoryIndexer $inventoryIndexer
    ) {}

    /**
     * Handle the adjust inventory quantity command.
     */
    public function handle(AdjustInventoryQtyDto $dto): CommandResponse
    {
        $retries = 3;
        $attempt = 0;

        while (true) {
            $attempt++;
            try {
                DB::transaction(function () use ($dto) {
                    $this->inventoryRepository->adjustQuantity(
                        $dto->productId,
                        $dto->inventorySourceId,
                        $dto->qtyChange,
                        $dto->userId
                    );
                });

                // Post-commit native inventory indexing trigger
                $product = $this->productRepository->find($dto->productId);
                if ($product) {
                    $this->inventoryIndexer->setProduct($product)->reindexBatch([$product]);
                }

                return CommandResponse::success([], 'Inventory adjusted successfully.');
            } catch (QueryException $e) {
                // Handle serialization failures/deadlocks (SQLSTATE 40001)
                $isDeadlock = $e->getCode() === '40001' || str_contains($e->getMessage(), 'Deadlock');
                if ($attempt < $retries && $isDeadlock) {
                    usleep(100000); // 100ms backoff before retry

                    continue;
                }
                throw $e;
            }
        }
    }
}
