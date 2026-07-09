<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Contracts\CommandBusInterface;
use App\Application\DTO\AdjustInventoryQtyDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryAdjustmentRequest;
use Illuminate\Http\JsonResponse;

class InventoryController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus
    ) {}

    /**
     * Store inventory adjustment.
     */
    public function store(InventoryAdjustmentRequest $request): JsonResponse
    {
        $this->validatePermission();

        $dto = new AdjustInventoryQtyDto(
            (int) $request->input('product_id'),
            (int) $request->input('inventory_source_id'),
            (int) $request->input('qty_change'),
            auth()->guard('admin')->user()?->id
        );

        $response = $this->commandBus->dispatch($dto);

        return response()->json([
            'success' => $response->isSuccess(),
            'message' => $response->getMessage(),
        ]);
    }

    /**
     * Validate current user permission.
     */
    private function validatePermission(): void
    {
        if (function_exists('bouncer') && ! bouncer()->hasPermission('catalog.products.edit')) {
            abort(403, 'Unauthorized.');
        }
    }
}
