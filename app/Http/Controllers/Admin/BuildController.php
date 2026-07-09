<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Contracts\CommandBusInterface;
use App\Application\DTO\AddBuildToCartDto;
use App\Application\DTO\CloneBuildDto;
use App\Application\DTO\DeleteBuildDto;
use App\Application\DTO\SaveBuildDto;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuildController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus
    ) {}

    /**
     * Create or update PC Build.
     */
    public function store(Request $request): JsonResponse
    {
        $dto = new SaveBuildDto(
            $request->input('build_id') ? (int) $request->input('build_id') : null,
            auth()->guard('admin')->user()?->id,
            $request->input('items', []),
            (int) $request->input('version', 1)
        );

        $response = $this->commandBus->dispatch($dto);

        return response()->json([
            'success' => $response->isSuccess(),
            'message' => $response->getMessage(),
            'data' => $response->getPayload(),
        ]);
    }

    /**
     * Clone PC Build.
     */
    public function clone(int $id): JsonResponse
    {
        $dto = new CloneBuildDto($id, auth()->guard('admin')->user()?->id);
        $response = $this->commandBus->dispatch($dto);

        return response()->json([
            'success' => $response->isSuccess(),
            'message' => $response->getMessage(),
            'data' => $response->getPayload(),
        ]);
    }

    /**
     * Delete PC Build.
     */
    public function destroy(int $id): JsonResponse
    {
        $dto = new DeleteBuildDto($id);
        $response = $this->commandBus->dispatch($dto);

        return response()->json([
            'success' => $response->isSuccess(),
            'message' => $response->getMessage(),
        ]);
    }

    /**
     * Add PC Build items to cart.
     */
    public function addToCart(int $id): JsonResponse
    {
        $dto = new AddBuildToCartDto($id);
        $response = $this->commandBus->dispatch($dto);

        return response()->json([
            'success' => $response->isSuccess(),
            'message' => $response->getMessage(),
        ]);
    }
}
