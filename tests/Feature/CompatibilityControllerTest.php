<?php

declare(strict_types=1);

use App\Application\DTO\CheckCompatibilityDto;
use App\Application\Handlers\CheckCompatibilityHandler;
use Illuminate\Support\Facades\DB;
use Webkul\Product\Models\ProductProxy;

if (! function_exists('setProductAttribute')) {
    function setProductAttribute(int $productId, string $attributeCode, mixed $value): void
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

        // Determine EAV storage column type
        $column = is_numeric($value) ? 'integer_value' : 'text_value';

        DB::table('product_attribute_values')->updateOrInsert([
            'product_id' => $productId,
            'attribute_id' => $attributeId,
        ], [
            $column => $value,
        ]);
    }
}

test('socket match returns compatible status', function () {
    DB::beginTransaction();

    $cpu = ProductProxy::create([
        'type' => 'simple',
        'attribute_family_id' => 1,
        'sku' => 'CPU-'.uniqid(),
    ]);
    setProductAttribute($cpu->id, 'cpu_socket', 'AM5');

    $motherboard = ProductProxy::create([
        'type' => 'simple',
        'attribute_family_id' => 1,
        'sku' => 'MB-'.uniqid(),
    ]);
    setProductAttribute($motherboard->id, 'cpu_socket', 'AM5');

    $dto = new CheckCompatibilityDto([
        'cpu' => $cpu->id,
        'motherboard' => $motherboard->id,
    ]);

    $handler = app(CheckCompatibilityHandler::class);
    $response = $handler->handle($dto);

    expect($response->isSuccess())->toBeTrue();
    $results = $response->getPayload();
    expect($results[0]['status'])->toBe('compatible');

    DB::rollBack();
});

test('socket mismatch returns incompatible status and code', function () {
    DB::beginTransaction();

    $cpu = ProductProxy::create([
        'type' => 'simple',
        'attribute_family_id' => 1,
        'sku' => 'CPU-'.uniqid(),
    ]);
    setProductAttribute($cpu->id, 'cpu_socket', 'LGA1700');

    $motherboard = ProductProxy::create([
        'type' => 'simple',
        'attribute_family_id' => 1,
        'sku' => 'MB-'.uniqid(),
    ]);
    setProductAttribute($motherboard->id, 'cpu_socket', 'AM5');

    $dto = new CheckCompatibilityDto([
        'cpu' => $cpu->id,
        'motherboard' => $motherboard->id,
    ]);

    $handler = app(CheckCompatibilityHandler::class);
    $response = $handler->handle($dto);

    expect($response->isSuccess())->toBeTrue();
    $results = $response->getPayload();
    expect($results[0]['status'])->toBe('incompatible');
    expect($results[0]['code'])->toBe('SOCKET_MISMATCH');

    DB::rollBack();
});

test('ram generation mismatch returns ram generation mismatch code', function () {
    DB::beginTransaction();

    $ram = ProductProxy::create([
        'type' => 'simple',
        'attribute_family_id' => 1,
        'sku' => 'RAM-'.uniqid(),
    ]);
    setProductAttribute($ram->id, 'ram_generation', 'DDR4');

    $motherboard = ProductProxy::create([
        'type' => 'simple',
        'attribute_family_id' => 1,
        'sku' => 'MB-'.uniqid(),
    ]);
    setProductAttribute($motherboard->id, 'motherboard_ram_generation', 'DDR5');

    $dto = new CheckCompatibilityDto([
        'ram' => $ram->id,
        'motherboard' => $motherboard->id,
    ]);

    $handler = app(CheckCompatibilityHandler::class);
    $response = $handler->handle($dto);

    expect($response->isSuccess())->toBeTrue();
    $results = $response->getPayload();
    expect($results[1]['status'])->toBe('incompatible');
    expect($results[1]['code'])->toBe('RAM_GENERATION_MISMATCH');

    DB::rollBack();
});

test('gpu length exceeds case clearance fails validation', function () {
    DB::beginTransaction();

    $gpu = ProductProxy::create([
        'type' => 'simple',
        'attribute_family_id' => 1,
        'sku' => 'GPU-'.uniqid(),
    ]);
    setProductAttribute($gpu->id, 'gpu_length', 340);

    $case = ProductProxy::create([
        'type' => 'simple',
        'attribute_family_id' => 1,
        'sku' => 'CASE-'.uniqid(),
    ]);
    setProductAttribute($case->id, 'case_gpu_clearance', 300);

    $dto = new CheckCompatibilityDto([
        'gpu' => $gpu->id,
        'case' => $case->id,
    ]);

    $handler = app(CheckCompatibilityHandler::class);
    $response = $handler->handle($dto);

    expect($response->isSuccess())->toBeTrue();
    $results = $response->getPayload();
    expect($results[2]['status'])->toBe('incompatible');
    expect($results[2]['code'])->toBe('GPU_TOO_LONG');

    DB::rollBack();
});

test('insufficient psu wattage fails power requirements', function () {
    DB::beginTransaction();

    $cpu = ProductProxy::create([
        'type' => 'simple',
        'attribute_family_id' => 1,
        'sku' => 'CPU-'.uniqid(),
    ]);
    setProductAttribute($cpu->id, 'power_draw', 125);

    $gpu = ProductProxy::create([
        'type' => 'simple',
        'attribute_family_id' => 1,
        'sku' => 'GPU-'.uniqid(),
    ]);
    setProductAttribute($gpu->id, 'power_draw', 320);

    $psu = ProductProxy::create([
        'type' => 'simple',
        'attribute_family_id' => 1,
        'sku' => 'PSU-'.uniqid(),
    ]);
    setProductAttribute($psu->id, 'psu_wattage', 450);

    $dto = new CheckCompatibilityDto([
        'cpu' => $cpu->id,
        'gpu' => $gpu->id,
        'psu' => $psu->id,
    ]);

    $handler = app(CheckCompatibilityHandler::class);
    $response = $handler->handle($dto);

    expect($response->isSuccess())->toBeTrue();
    $results = $response->getPayload();
    expect($results[4]['status'])->toBe('incompatible');
    expect($results[4]['code'])->toBe('INSUFFICIENT_PSU_WATTAGE');

    DB::rollBack();
});
