<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use App\Application\Authorization\AuthorizerInterface;
use App\Application\DTO\CreateBrandDto;
use App\Application\Exceptions\ValidationException;
use App\Application\Responses\CommandResponse;
use App\Domain\Events\BrandCreated;
use App\Domain\Factories\BrandFactory;
use App\Domain\Repositories\BrandRepositoryInterface;
use App\Domain\Specifications\BrandSpecification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        $uploadedFiles = [];
        try {
            // 3. Database Transaction
            return DB::transaction(function () use ($dto, &$uploadedFiles) {
                $logoLightPath = null;
                $logoDarkPath = null;

                if ($dto->getLogoLight()) {
                    $logoLightPath = 'brand/logos/' . Str::random(40) . '.webp';
                    $encoded = image_manager()->read($dto->getLogoLight())->encodeByExtension('webp');
                    Storage::put($logoLightPath, (string) $encoded);
                    $uploadedFiles[] = $logoLightPath;
                }

                if ($dto->getLogoDark()) {
                    $logoDarkPath = 'brand/logos/' . Str::random(40) . '.webp';
                    $encoded = image_manager()->read($dto->getLogoDark())->encodeByExtension('webp');
                    Storage::put($logoDarkPath, (string) $encoded);
                    $uploadedFiles[] = $logoDarkPath;
                }

                $brand = $this->factory->create(
                    $dto->getName(),
                    $dto->getSlug(),
                    $logoLightPath,
                    $logoDarkPath,
                    $dto->getWebsiteUrl(),
                    $dto->getStatus(),
                    $dto->getDescription(),
                    $dto->getMetaTitle(),
                    $dto->getMetaKeywords(),
                    $dto->getMetaDescription(),
                    $dto->getSortOrder()
                );

                // 4. Domain Validation via Specification
                if (! $this->specification->isSatisfiedBy($brand)) {
                    throw new ValidationException('Domain specification validation failed for Brand.');
                }

                // 5. Persist
                $saved = $this->repository->create([
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                    'logo_light' => $brand->logo_light,
                    'logo_dark' => $brand->logo_dark,
                    'website_url' => $brand->website_url,
                    'status' => $brand->status,
                    'description' => $brand->description,
                    'meta_title' => $brand->meta_title,
                    'meta_keywords' => $brand->meta_keywords,
                    'meta_description' => $brand->meta_description,
                    'sort_order' => $brand->sort_order,
                ]);

                event(new BrandCreated($saved));

                return CommandResponse::success(
                    ['id' => $saved->id, 'slug' => $saved->slug],
                    'Brand created successfully.'
                );
            });
        } catch (\Throwable $e) {
            foreach ($uploadedFiles as $path) {
                Storage::delete($path);
            }
            throw $e;
        }
    }
}
