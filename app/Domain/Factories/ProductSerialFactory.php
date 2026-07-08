<?php

declare(strict_types=1);

namespace App\Domain\Factories;

use App\Domain\Enums\SerialStatus;
use App\Domain\Models\ProductSerial;
use App\Domain\ValueObjects\SerialNumber;

final class ProductSerialFactory
{
    /**
     * Create a new ProductSerial model instance.
     */
    public function create(int $productId, int $inventorySourceId, SerialNumber $serialNumber, SerialStatus $status = SerialStatus::AVAILABLE): ProductSerial
    {
        return new ProductSerial([
            'product_id' => $productId,
            'inventory_source_id' => $inventorySourceId,
            'serial_number' => $serialNumber->getValue(),
            'status' => $status->value,
        ]);
    }
}
