<?php

declare(strict_types=1);

use App\Application\Authorization\AuthorizerInterface;
use App\Application\Authorization\SimpleAuthorizer;
use App\Application\Contracts\CommandBusInterface;
use App\Application\Contracts\QueryBusInterface;
use App\Application\DTO\AssignBrandToProductDto;
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
use App\Application\Queries\GetBrandBySlugQuery;
use App\Application\Queries\GetProductWithBrandQuery;
use App\Domain\Enums\CompatibilityType;
use App\Domain\Enums\SerialStatus;
use App\Domain\Repositories\BrandRepositoryInterface;
use App\Domain\ValueObjects\SerialNumber;
use App\Domain\ValueObjects\Slug;
use Illuminate\Support\Facades\DB;
use Webkul\Product\Models\ProductProxy;

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

test('command bus dispatches commands to handlers', function () {
    DB::beginTransaction();

    /** @var SimpleAuthorizer $authorizer */
    $authorizer = app(AuthorizerInterface::class);
    $authorizer->setShouldPass(true);

    $bus = app(CommandBusInterface::class);
    $dto = new CreateBrandDto(new Slug('gigabyte-aero'), 'light.jpg', 'dark.jpg', 'https://gigabyte.com');

    $response = $bus->dispatch($dto);

    expect($response->isSuccess())->toBeTrue();
    expect($response->getPayload()['slug'])->toBe('gigabyte-aero');

    DB::rollBack();
});

test('query bus asks queries to query handlers', function () {
    DB::beginTransaction();

    /** @var SimpleAuthorizer $authorizer */
    $authorizer = app(AuthorizerInterface::class);
    $authorizer->setShouldPass(true);

    // Create a brand first
    $createHandler = app(CreateBrandHandler::class);
    $createDto = new CreateBrandDto(new Slug('razer-blade'));
    $createHandler->handle($createDto);

    $bus = app(QueryBusInterface::class);
    $query = new GetBrandBySlugQuery('razer-blade');

    $brand = $bus->ask($query);

    expect($brand)->not->toBeNull();
    expect($brand->slug)->toBe('razer-blade');

    DB::rollBack();
});

test('command and query bus executes product brand assignment and dynamic relation loading', function () {
    DB::beginTransaction();

    /** @var SimpleAuthorizer $authorizer */
    $authorizer = app(AuthorizerInterface::class);
    $authorizer->setShouldPass(true);

    // 1. Create a brand
    $brandRepo = app(BrandRepositoryInterface::class);
    $brand = $brandRepo->create([
        'slug' => 'msi-gaming',
        'website_url' => 'https://msi.com',
    ]);

    // 2. Create a mock product
    $product = ProductProxy::create([
        'type' => 'simple',
        'attribute_family_id' => 1,
        'sku' => 'MSI-GL65-999',
    ]);

    // 3. Assign brand using Command Bus
    $bus = app(CommandBusInterface::class);
    $dto = new AssignBrandToProductDto($product->id, $brand->id);
    $response = $bus->dispatch($dto);

    expect($response->isSuccess())->toBeTrue();
    expect($response->getPayload()['brand_id'])->toBe($brand->id);

    // 4. Query product with Brand eager loaded using Query Bus
    $queryBus = app(QueryBusInterface::class);
    $query = new GetProductWithBrandQuery($product->id);
    $fetchedProduct = $queryBus->ask($query);

    expect($fetchedProduct)->not->toBeNull();
    expect($fetchedProduct->brand)->not->toBeNull();
    expect($fetchedProduct->brand->slug)->toBe('msi-gaming');

    DB::rollBack();
});
