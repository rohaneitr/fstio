<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use App\Application\Authorization\AuthorizerInterface;
use App\Application\DTO\CreateProductSerialDto;
use App\Application\Exceptions\ValidationException;
use App\Application\Responses\CommandResponse;
use App\Domain\Factories\ProductSerialFactory;
use App\Domain\Repositories\ProductSerialRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class CreateProductSerialHandler
{
    public function __construct(
        private AuthorizerInterface $authorizer,
        private ProductSerialRepositoryInterface $repository,
        private ProductSerialFactory $factory
    ) {}

    public function handle(CreateProductSerialDto $dto): CommandResponse
    {
        // 1. Authorize
        $this->authorizer->authorize('create_serial');

        // 2. Application validation (No duplicates)
        $existing = DB::table('product_serials')
            ->where('serial_number', $dto->getSerialNumber()->getValue())
            ->first();

        if ($existing) {
            throw new ValidationException('Product serial number already exists.');
        }

        // 3. Create in Transaction
        return DB::transaction(function () use ($dto) {
            $serial = $this->factory->create(
                $dto->getProductId(),
                $dto->getInventorySourceId(),
                $dto->getSerialNumber(),
                $dto->getStatus()
            );

            $saved = $this->repository->create([
                'product_id' => $serial->product_id,
                'inventory_source_id' => $serial->inventory_source_id,
                'serial_number' => $serial->serial_number,
                'status' => $serial->status,
            ]);

            return CommandResponse::success(
                ['id' => $saved->id, 'serial_number' => $saved->serial_number],
                'Product serial created successfully.'
            );
        });
    }
}
