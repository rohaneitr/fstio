<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use App\Application\Authorization\AuthorizerInterface;
use App\Application\DTO\UpdateBrandDto;
use App\Application\Exceptions\ValidationException;
use App\Application\Responses\CommandResponse;
use App\Domain\Repositories\BrandRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class UpdateBrandHandler
{
    public function __construct(
        private AuthorizerInterface $authorizer,
        private BrandRepositoryInterface $repository
    ) {}

    public function handle(UpdateBrandDto $dto): CommandResponse
    {
        // 1. Authorize
        $this->authorizer->authorize('update_brand');

        // 2. Validate existence
        $brand = $this->repository->find($dto->getId());
        if (! $brand) {
            throw new ValidationException('Brand not found.');
        }

        // 3. Update in Transaction
        return DB::transaction(function () use ($dto) {
            $updated = $this->repository->update([
                'logo_light' => $dto->getLogoLight(),
                'logo_dark' => $dto->getLogoDark(),
                'website_url' => $dto->getWebsiteUrl(),
            ], $dto->getId());

            return CommandResponse::success(
                ['id' => $updated->id, 'slug' => $updated->slug],
                'Brand updated successfully.'
            );
        });
    }
}
