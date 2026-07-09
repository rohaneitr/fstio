<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use App\Application\DTO\DeleteBuildDto;
use App\Application\Responses\CommandResponse;
use App\Domain\Repositories\BuildRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class DeleteBuildHandler
{
    public function __construct(
        private BuildRepositoryInterface $buildRepository
    ) {}

    /**
     * Handle deleting a PC Build.
     */
    public function handle(DeleteBuildDto $dto): CommandResponse
    {
        return DB::transaction(function () use ($dto) {
            $build = $this->buildRepository->find($dto->buildId);
            if (! $build) {
                return CommandResponse::failure('PC Build not found.');
            }

            $this->buildRepository->delete($dto->buildId);

            return CommandResponse::success([], 'PC Build deleted successfully.');
        });
    }
}
