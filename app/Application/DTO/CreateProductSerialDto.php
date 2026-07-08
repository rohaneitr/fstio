<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Enums\SerialStatus;
use App\Domain\ValueObjects\SerialNumber;

final readonly class CreateProductSerialDto
{
    public function __construct(
        private int $productId,
        private int $inventorySourceId,
        private SerialNumber $serialNumber,
        private SerialStatus $status = SerialStatus::AVAILABLE
    ) {}

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getInventorySourceId(): int
    {
        return $this->inventorySourceId;
    }

    public function getSerialNumber(): SerialNumber
    {
        return $this->serialNumber;
    }

    public function getStatus(): SerialStatus
    {
        return $this->status;
    }
}
