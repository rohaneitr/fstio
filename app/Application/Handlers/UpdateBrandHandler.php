<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use App\Application\Authorization\AuthorizerInterface;
use App\Application\DTO\UpdateBrandDto;
use App\Application\Exceptions\ValidationException;
use App\Application\Responses\CommandResponse;
use App\Domain\Events\BrandUpdated;
use App\Domain\Repositories\BrandRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        // Validate slug uniqueness if updated
        $existing = $this->repository->findBySlug($dto->getSlug()->getValue());
        if ($existing && $existing->id !== $dto->getId()) {
            throw new ValidationException('Brand with this slug already exists.');
        }

        $uploadedFiles = [];
        $obsoleteFiles = [];

        try {
            // 3. Update in Transaction
            return DB::transaction(function () use ($dto, $brand, &$uploadedFiles, &$obsoleteFiles) {
                $logoLightPath = $brand->logo_light;
                $logoDarkPath = $brand->logo_dark;

                // Handle logo light deletion or replacement
                if ($dto->getDeleteLogoLight() || $dto->getLogoLight()) {
                    if ($brand->logo_light) {
                        $obsoleteFiles[] = $brand->logo_light;
                        $logoLightPath = null;
                    }
                }
                if ($dto->getLogoLight()) {
                    $logoLightPath = 'brand/logos/'.Str::random(40).'.webp';
                    $encoded = image_manager()->read($dto->getLogoLight())->encodeByExtension('webp');
                    Storage::put($logoLightPath, (string) $encoded);
                    $uploadedFiles[] = $logoLightPath;
                }

                // Handle logo dark deletion or replacement
                if ($dto->getDeleteLogoDark() || $dto->getLogoDark()) {
                    if ($brand->logo_dark) {
                        $obsoleteFiles[] = $brand->logo_dark;
                        $logoDarkPath = null;
                    }
                }
                if ($dto->getLogoDark()) {
                    $logoDarkPath = 'brand/logos/'.Str::random(40).'.webp';
                    $encoded = image_manager()->read($dto->getLogoDark())->encodeByExtension('webp');
                    Storage::put($logoDarkPath, (string) $encoded);
                    $uploadedFiles[] = $logoDarkPath;
                }

                $updated = $this->repository->update([
                    'name' => $dto->getName(),
                    'slug' => $dto->getSlug()->getValue(),
                    'logo_light' => $logoLightPath,
                    'logo_dark' => $logoDarkPath,
                    'website_url' => $dto->getWebsiteUrl(),
                    'status' => $dto->getStatus(),
                    'description' => $dto->getDescription(),
                    'meta_title' => $dto->getMetaTitle(),
                    'meta_keywords' => $dto->getMetaKeywords(),
                    'meta_description' => $dto->getMetaDescription(),
                    'sort_order' => $dto->getSortOrder(),
                ], $dto->getId());

                // Post-commit: Clean up obsolete files
                foreach ($obsoleteFiles as $path) {
                    Storage::delete($path);
                }

                event(new BrandUpdated($updated));

                return CommandResponse::success(
                    ['id' => $updated->id, 'slug' => $updated->slug],
                    'Brand updated successfully.'
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
