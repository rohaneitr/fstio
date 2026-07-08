<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use App\Application\Authorization\AuthorizerInterface;
use App\Application\Exceptions\ValidationException;
use App\Application\Responses\CommandResponse;
use App\Domain\Repositories\BrandRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class DeleteBrandHandler
{
    public function __construct(
        private AuthorizerInterface $authorizer,
        private BrandRepositoryInterface $repository
    ) {}

    public function handle(int $id): CommandResponse
    {
        // 1. Authorize
        $this->authorizer->authorize('delete_brand');

        // 2. Validate existence
        if (! $this->repository->find($id)) {
            throw new ValidationException('Brand not found.');
        }

        // 3. Delete in Transaction
        return DB::transaction(function () use ($id) {
            $this->repository->delete($id);

            return CommandResponse::success([], 'Brand deleted successfully.');
        });
    }
}
