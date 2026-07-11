<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use App\Application\DTO\CloneBuildDto;
use App\Application\Responses\CommandResponse;
use App\Domain\Repositories\BuildRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class CloneBuildHandler
{
    public function __construct(
        private BuildRepositoryInterface $buildRepository
    ) {}

    /**
     * Handle cloning a PC Build.
     */
    public function handle(CloneBuildDto $dto): CommandResponse
    {
        return DB::transaction(function () use ($dto) {
            $original = $this->buildRepository->find($dto->buildId);
            if (! $original) {
                return CommandResponse::failure('Original PC Build not found.');
            }

            $cloned = $this->buildRepository->create([
                'uuid' => (string) Str::uuid(),
                'name' => $original->name ? 'Clone of '.$original->name : null,
                'user_id' => $dto->userId ?? $original->user_id,
                'total_price' => $original->total_price,
                'estimated_wattage' => $original->estimated_wattage,
                'version' => 1,
                'build_hash' => $original->build_hash,
            ]);

            foreach ($original->items as $item) {
                $cloned->items()->create([
                    'product_id' => $item->product_id,
                    'component_type' => $item->component_type,
                ]);
            }

            return CommandResponse::success([
                'id' => $cloned->id,
                'uuid' => $cloned->uuid,
                'version' => $cloned->version,
            ], 'PC Build cloned successfully.');
        });
    }
}
