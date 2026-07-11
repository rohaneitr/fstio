<?php

declare(strict_types=1);

use App\Application\DTO\AddBuildToCartDto;
use App\Application\DTO\CloneBuildDto;
use App\Application\DTO\DeleteBuildDto;
use App\Application\DTO\SaveBuildDto;
use App\Application\Handlers\AddBuildToCartHandler;
use App\Application\Handlers\CloneBuildHandler;
use App\Application\Handlers\DeleteBuildHandler;
use App\Application\Handlers\SaveBuildHandler;
use App\Domain\Models\Build;
use Illuminate\Support\Facades\DB;
use Webkul\Product\Models\ProductProxy;

function setPCBuilderProductAttribute(int $productId, string $attributeCode, mixed $value): void
{
    $attribute = DB::table('attributes')->where('code', $attributeCode)->first();
    if (! $attribute) {
        $attributeId = DB::table('attributes')->insertGetId([
            'code' => $attributeCode,
            'admin_name' => $attributeCode,
            'type' => 'text',
            'position' => 1,
            'is_required' => 0,
            'is_unique' => 0,
        ]);
    } else {
        $attributeId = $attribute->id;
    }

    $column = 'text_value';
    if (is_float($value)) {
        $column = 'float_value';
    } elseif (is_int($value)) {
        $column = 'integer_value';
    }

    DB::table('product_attribute_values')->updateOrInsert([
        'product_id' => $productId,
        'attribute_id' => $attributeId,
    ], [
        $column => $value,
    ]);
}

function setPCBuilderProductInventory(int $productId, int $qty = 100): void
{
    $sourceId = DB::table('inventory_sources')->value('id') ?? 1;
    $channelId = DB::table('channels')->value('id') ?? 1;

    DB::table('product_inventories')->updateOrInsert([
        'product_id' => $productId,
        'inventory_source_id' => $sourceId,
    ], [
        'qty' => $qty,
    ]);

    DB::table('product_inventory_indices')->updateOrInsert([
        'product_id' => $productId,
        'channel_id' => $channelId,
    ], [
        'qty' => $qty,
    ]);
}

function createPCBuilderTestProduct(string $sku, float $price = 99.99): mixed
{
    $product = ProductProxy::create([
        'type' => 'simple',
        'attribute_family_id' => 1,
        'sku' => $sku,
    ]);

    setPCBuilderProductInventory($product->id, 100);
    setPCBuilderProductAttribute($product->id, 'price', $price);
    setPCBuilderProductAttribute($product->id, 'name', $sku);

    $channelId = DB::table('channels')->value('id') ?? 1;
    $customerGroupId = DB::table('customer_groups')->value('id') ?? 1;

    DB::table('product_price_indices')->updateOrInsert([
        'product_id' => $product->id,
        'channel_id' => $channelId,
        'customer_group_id' => $customerGroupId,
    ], [
        'min_price' => $price,
        'max_price' => $price,
    ]);

    $channelCode = DB::table('channels')->value('code') ?? 'default';
    $localeCode = DB::table('locales')->value('code') ?? 'en';

    DB::table('product_flat')->updateOrInsert([
        'product_id' => $product->id,
        'channel' => $channelCode,
        'locale' => $localeCode,
    ], [
        'price' => $price,
        'name' => $sku,
        'sku' => $sku,
        'status' => 1,
        'visible_individually' => 1,
        'url_key' => 'product-'.$product->id,
    ]);

    return $product;
}

test('save build creates build and items successfully', function () {
    DB::beginTransaction();

    $cpu = createPCBuilderTestProduct('CPU-'.uniqid(), 299.99);

    $dto = new SaveBuildDto(
        buildId: null,
        userId: null,
        items: [
            'cpu' => $cpu->id,
        ],
        version: 1
    );

    $handler = app(SaveBuildHandler::class);
    $response = $handler->handle($dto);

    expect($response->isSuccess())->toBeTrue();
    $data = $response->getPayload();

    expect($data['version'])->toBe(1);
    expect($data['total_price'])->toBe(299.99);

    // Verify database records
    $build = Build::find($data['id']);
    expect($build)->not->toBeNull();
    expect($build->items)->toHaveCount(1);
    expect($build->items->first()->product_id)->toBe($cpu->id);

    DB::rollBack();
});

test('save build increments version and syncs items upon updates', function () {
    DB::beginTransaction();

    $cpu = createPCBuilderTestProduct('CPU-'.uniqid(), 299.99);

    $handler = app(SaveBuildHandler::class);

    // First save
    $dto1 = new SaveBuildDto(null, null, ['cpu' => $cpu->id], 1);
    $res1 = $handler->handle($dto1)->getPayload();

    $gpu = createPCBuilderTestProduct('GPU-'.uniqid(), 499.99);

    // Update save
    $dto2 = new SaveBuildDto(
        buildId: $res1['id'],
        userId: null,
        items: [
            'cpu' => $cpu->id,
            'gpu' => $gpu->id,
        ],
        version: 1
    );

    $res2 = $handler->handle($dto2)->getPayload();
    expect($res2['version'])->toBe(2);
    expect($res2['total_price'])->toBe(799.98);

    $build = Build::find($res1['id']);
    expect($build->items)->toHaveCount(2);

    DB::rollBack();
});

test('optimistic locking mismatch fails update save', function () {
    DB::beginTransaction();

    $cpu = createPCBuilderTestProduct('CPU-'.uniqid());

    $handler = app(SaveBuildHandler::class);
    $res1 = $handler->handle(new SaveBuildDto(null, null, ['cpu' => $cpu->id], 1))->getPayload();

    // Try updating with outdated version (e.g. 5 instead of 1)
    $dto = new SaveBuildDto($res1['id'], null, ['cpu' => $cpu->id], 5);

    expect(fn () => $handler->handle($dto))->toThrow(Exception::class, 'Version mismatch');

    DB::rollBack();
});

test('clone build duplicates build record with new uuid and version resets to one', function () {
    DB::beginTransaction();

    $cpu = createPCBuilderTestProduct('CPU-'.uniqid());

    $handler = app(SaveBuildHandler::class);
    $res1 = $handler->handle(new SaveBuildDto(null, null, ['cpu' => $cpu->id], 1))->getPayload();

    // Clone it
    $cloneHandler = app(CloneBuildHandler::class);
    $resClone = $cloneHandler->handle(new CloneBuildDto($res1['id']))->getPayload();

    expect($resClone['id'])->not->toBe($res1['id']);
    expect($resClone['uuid'])->not->toBe($res1['uuid']);
    expect($resClone['version'])->toBe(1);

    $clonedBuild = Build::find($resClone['id']);
    expect($clonedBuild->items)->toHaveCount(1);

    DB::rollBack();
});

test('delete build removes build and cascaded items', function () {
    DB::beginTransaction();

    $cpu = createPCBuilderTestProduct('CPU-'.uniqid());

    $handler = app(SaveBuildHandler::class);
    $res = $handler->handle(new SaveBuildDto(null, null, ['cpu' => $cpu->id], 1))->getPayload();

    $buildId = $res['id'];
    expect(Build::find($buildId))->not->toBeNull();

    // Delete it
    $deleteHandler = app(DeleteBuildHandler::class);
    $deleteHandler->handle(new DeleteBuildDto($buildId));

    expect(Build::find($buildId))->toBeNull();
    expect(DB::table('pc_build_items')->where('build_id', $buildId)->count())->toBe(0);

    DB::rollBack();
});

test('add build to cart runs successfully', function () {
    DB::beginTransaction();

    $cpu = createPCBuilderTestProduct('CPU-'.uniqid(), 299.99);

    $handler = app(SaveBuildHandler::class);
    $res = $handler->handle(new SaveBuildDto(null, null, ['cpu' => $cpu->id], 1))->getPayload();

    $cartHandler = app(AddBuildToCartHandler::class);
    $response = $cartHandler->handle(new AddBuildToCartDto($res['id']));

    expect($response->isSuccess())->toBeTrue();

    DB::rollBack();
});

test('save build with custom name works and clone build copies name with prefix', function () {
    DB::beginTransaction();

    $cpu = createPCBuilderTestProduct('CPU-'.uniqid(), 299.99);

    $dto = new SaveBuildDto(
        buildId: null,
        userId: null,
        items: ['cpu' => $cpu->id],
        version: 1,
        name: 'My Special Workstation'
    );

    $handler = app(SaveBuildHandler::class);
    $response = $handler->handle($dto);

    expect($response->isSuccess())->toBeTrue();
    $data = $response->getPayload();

    $build = Build::find($data['id']);
    expect($build->name)->toBe('My Special Workstation');

    // Clone it
    $cloneHandler = app(CloneBuildHandler::class);
    $resClone = $cloneHandler->handle(new CloneBuildDto($build->id))->getPayload();

    $clonedBuild = Build::find($resClone['id']);
    expect($clonedBuild->name)->toBe('Clone of My Special Workstation');

    DB::rollBack();
});

test('storefront pc builder api endpoints return correct json structure', function () {
    DB::beginTransaction();

    $cpu = createPCBuilderTestProduct('CPU-test-sf', 199.99);
    setPCBuilderProductAttribute($cpu->id, 'supported_chipsets', 'B650');

    // Test products endpoint
    $responseProducts = $this->getJson(route('shop.pc-builder.api.products', ['type' => 'cpu', 'search' => 'CPU-test-sf']));
    $responseProducts->assertStatus(200);
    $responseProducts->assertJsonStructure([
        'data' => [
            '*' => ['id', 'sku', 'name', 'price', 'formatted_price', 'in_stock', 'stock_qty', 'url_key'],
        ],
        'current_page',
        'last_page',
        'total',
    ]);

    // Test compatibility check endpoint
    $responseComp = $this->postJson(route('shop.pc-builder.api.compatibility'), [
        'items' => ['cpu' => $cpu->id],
    ]);
    $responseComp->assertStatus(200);
    $responseComp->assertJsonStructure([
        'success',
        'message',
        'data' => [],
    ]);

    // Test save build endpoint
    $responseSave = $this->postJson(route('shop.pc-builder.api.save'), [
        'items' => ['cpu' => $cpu->id],
        'name' => 'Storefront Saved Build',
        'version' => 1,
    ]);
    $responseSave->assertStatus(200);
    $responseSave->assertJsonStructure([
        'success',
        'message',
        'data' => ['id', 'uuid', 'version', 'total_price'],
    ]);

    DB::rollBack();
});
