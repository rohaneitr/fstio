<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use App\Application\Authorization\AuthorizerInterface;
use App\Application\DTO\CreateBrandDto;
use App\Application\Exceptions\ValidationException;
use App\Application\Responses\CommandResponse;
use App\Domain\Factories\BrandFactory;
use App\Domain\Repositories\BrandRepositoryInterface;
use App\Domain\Specifications\BrandSpecification;
use Illuminate\Support\Facades\DB;

final readonly class CreateBrandHandler
{
    public function __construct(
        private AuthorizerInterface $authorizer,
        private BrandRepositoryInterface $repository,
        private BrandFactory $factory,
        private BrandSpecification $specification
    ) {}

    public function handle(CreateBrandDto $dto): CommandResponse
    {
        // 1. Authorize
        $this->authorizer->authorize('create_brand');

        // 2. Application Validation
        if ($this->repository->findBySlug($dto->getSlug()->getValue())) {
            throw new ValidationException('Brand with this slug already exists.');
        }

        // 3. Database Transaction
        return DB::transaction(function () use ($dto) {
            $brand = $this->factory->create(
                $dto->getSlug(),
                $dto->getLogoLight(),
                $dto->getLogoDark(),
                $dto->getWebsiteUrl()
            );

            // 4. Domain Validation via Specification
            if (! $this->specification->isSatisfiedBy($brand)) {
                throw new ValidationException('Domain specification validation failed for Brand.');
            }

            // 5. Persist
            $saved = $this->repository->create([
                'slug' => $brand->slug,
                'logo_light' => $brand->logo_light,
                'logo_dark' => $brand->logo_dark,
                'website_url' => $brand->website_url,
            ]);

            return CommandResponse::success(
                ['id' => $saved->id, 'slug' => $saved->slug],
                'Brand created successfully.'
            );
        });
    }
}
