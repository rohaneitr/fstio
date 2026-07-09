<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Contracts\CommandBusInterface;
use App\Application\DTO\GetRecommendationsDto;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus
    ) {}

    /**
     * Get dynamic product recommendations.
     */
    public function index(Request $request, int $productId): JsonResponse
    {
        $dto = new GetRecommendationsDto(
            $productId,
            $request->input('strategy', 'similar')
        );

        $response = $this->commandBus->dispatch($dto);

        return response()->json([
            'success' => $response->isSuccess(),
            'message' => $response->getMessage(),
            'data' => $response->getPayload(),
        ]);
    }
}
