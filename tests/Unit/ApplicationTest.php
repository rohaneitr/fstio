<?php

declare(strict_types=1);

uses(TestCase::class);

use App\Application\Authorization\AuthorizerInterface;
use App\Application\Authorization\SimpleAuthorizer;
use App\Application\DTO\CreateBrandDto;
use App\Application\DTO\CreateProductSerialDto;
use App\Application\DTO\RegisterCompatibilityRuleDto;
use App\Application\DTO\UpdateBrandDto;
use App\Application\Exceptions\AuthorizationException;
use App\Application\Exceptions\ValidationException;
use App\Application\Handlers\CreateBrandHandler;
use App\Application\Handlers\CreateProductSerialHandler;
use App\Application\Handlers\DeleteBrandHandler;
use App\Application\Handlers\RegisterCompatibilityRuleHandler;
use App\Application\Handlers\UpdateBrandHandler;
use App\Domain\Enums\CompatibilityType;
use App\Domain\Enums\SerialStatus;
use App\Domain\Repositories\BrandRepositoryInterface;
use App\Domain\ValueObjects\SerialNumber;
use App\Domain\ValueObjects\Slug;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

test('create brand handler creates brand successfully', function () {
    DB::beginTransaction();

    /** @var SimpleAuthorizer $authorizer */
    $authorizer = app(AuthorizerInterface::class);
    $authorizer->setShouldPass(true);

    $handler = app(CreateBrandHandler::class);
    $dto = new CreateBrandDto(new Slug('acer-nitro'), 'light.jpg', 'dark.jpg', 'https://acer.com');

    $response = $handler->handle($dto);

    expect($response->isSuccess())->toBeTrue();
    expect($response->getMessage())->toBe('Brand created successfully.');
    expect($response->getPayload()['slug'])->toBe('acer-nitro');

    DB::rollBack();
});

test('create brand handler prevents duplicate slugs and throws ValidationException', function () {
    DB::beginTransaction();

    $handler = app(CreateBrandHandler::class);
    $dto = new CreateBrandDto(new Slug('asus-rog'));

    // Create first
    $handler->handle($dto);

    // Try second
    $this->expectException(ValidationException::class);
    $handler->handle($dto);

    DB::rollBack();
});

test('create brand handler respects authorization checks', function () {
    /** @var SimpleAuthorizer $authorizer */
    $authorizer = app(AuthorizerInterface::class);
    $authorizer->setShouldPass(false);

    $handler = app(CreateBrandHandler::class);
    $dto = new CreateBrandDto(new Slug('gigabyte-aorus'));

    $this->expectException(AuthorizationException::class);
    $handler->handle($dto);
});

test('update brand handler edits brand fields successfully', function () {
    DB::beginTransaction();

    /** @var SimpleAuthorizer $authorizer */
    $authorizer = app(AuthorizerInterface::class);
    $authorizer->setShouldPass(true);

    $createHandler = app(CreateBrandHandler::class);
    $updateHandler = app(UpdateBrandHandler::class);

    $createDto = new CreateBrandDto(new Slug('msi-gaming'));
    $created = $createHandler->handle($createDto);
    $brandId = $created->getPayload()['id'];

    $updateDto = new UpdateBrandDto($brandId, 'new_light.jpg', 'new_dark.jpg', 'https://msi.com');
    $response = $updateHandler->handle($updateDto);

    expect($response->isSuccess())->toBeTrue();
    expect($response->getMessage())->toBe('Brand updated successfully.');

    $repo = app(BrandRepositoryInterface::class);
    $updatedBrand = $repo->find($brandId);
    expect($updatedBrand->website_url)->toBe('https://msi.com');

    DB::rollBack();
});

test('delete brand handler removes brand record successfully', function () {
    DB::beginTransaction();

    /** @var SimpleAuthorizer $authorizer */
    $authorizer = app(AuthorizerInterface::class);
    $authorizer->setShouldPass(true);

    $createHandler = app(CreateBrandHandler::class);
    $deleteHandler = app(DeleteBrandHandler::class);

    $createDto = new CreateBrandDto(new Slug('hp-omen'));
    $created = $createHandler->handle($createDto);
    $brandId = $created->getPayload()['id'];

    $response = $deleteHandler->handle($brandId);
    expect($response->isSuccess())->toBeTrue();

    $repo = app(BrandRepositoryInterface::class);
    expect($repo->find($brandId))->toBeNull();

    DB::rollBack();
});

test('create product serial handler creates serial successfully', function () {
    DB::beginTransaction();

    /** @var SimpleAuthorizer $authorizer */
    $authorizer = app(AuthorizerInterface::class);
    $authorizer->setShouldPass(true);

    $handler = app(CreateProductSerialHandler::class);
    $dto = new CreateProductSerialDto(10, 1, new SerialNumber('SN-NITRO-999'), SerialStatus::AVAILABLE);

    $response = $handler->handle($dto);
    expect($response->isSuccess())->toBeTrue();
    expect($response->getPayload()['serial_number'])->toBe('SN-NITRO-999');

    // Duplicate check
    $this->expectException(ValidationException::class);
    $handler->handle($dto);

    DB::rollBack();
});

test('register compatibility rule handler checks domain specifications', function () {
    DB::beginTransaction();

    /** @var SimpleAuthorizer $authorizer */
    $authorizer = app(AuthorizerInterface::class);
    $authorizer->setShouldPass(true);

    $handler = app(RegisterCompatibilityRuleHandler::class);

    // Identical product IDs should trigger domain specification failure
    $dtoBad = new RegisterCompatibilityRuleDto(5, 5, CompatibilityType::SOCKET);

    $this->expectException(ValidationException::class);
    $handler->handle($dtoBad);

    DB::rollBack();
});
