<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use App\Application\Authorization\AuthorizerInterface;
use App\Application\DTO\DeleteBrandDto;
use App\Application\Exceptions\ValidationException;
use App\Application\Responses\CommandResponse;
use App\Domain\Events\BrandDeleted;
use App\Domain\Repositories\BrandRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final readonly class DeleteBrandHandler
{
    public function __construct(
        private AuthorizerInterface $authorizer,
        private BrandRepositoryInterface $repository
    ) {}

    public function handle(DeleteBrandDto $dto): CommandResponse
    {
        // 1. Authorize
        $this->authorizer->authorize('delete_brand');

        $id = $dto->getId();

        // 2. Validate existence
        $brand = $this->repository->find($id);
        if (! $brand) {
            throw new ValidationException('Brand not found.');
        }

        // 3. Delete in Transaction
        return DB::transaction(function () use ($id, $brand) {
            $this->repository->delete($id);

            // Clean up files
            if ($brand->logo_light) {
                Storage::delete($brand->logo_light);
            }
            if ($brand->logo_dark) {
                Storage::delete($brand->logo_dark);
            }

            event(new BrandDeleted($id));

            return CommandResponse::success([], 'Brand deleted successfully.');
        });
    }
}
