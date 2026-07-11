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
    expect($results[4]['code'])->toBe('INSUFFICIENT_PSU_WATTAGE');

    DB::rollBack();
});

test('chipset compatibility rule validates cpu chipset support', function () {
    DB::beginTransaction();

    $cpu = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'CPU-'.uniqid()]);
    setProductAttribute($cpu->id, 'supported_chipsets', 'B650,X670');

    $motherboard = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'MB-'.uniqid()]);
    setProductAttribute($motherboard->id, 'motherboard_chipset', 'A620'); // Mismatch

    $dto = new CheckCompatibilityDto([
        'cpu' => $cpu->id,
        'motherboard' => $motherboard->id,
    ]);

    $handler = app(CheckCompatibilityHandler::class);
    $results = $handler->handle($dto)->getPayload();

    expect($results[5]['status'])->toBe('incompatible');
    expect($results[5]['code'])->toBe('CHIPSET_MISMATCH');

    DB::rollBack();
});

test('bios version rule warns on outdated motherboard bios', function () {
    DB::beginTransaction();

    $cpu = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'CPU-'.uniqid()]);
    setProductAttribute($cpu->id, 'cpu_required_bios', 'F10');

    $motherboard = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'MB-'.uniqid()]);
    setProductAttribute($motherboard->id, 'motherboard_bios_version', 'F2'); // Lower version

    $dto = new CheckCompatibilityDto([
        'cpu' => $cpu->id,
        'motherboard' => $motherboard->id,
    ]);

    $handler = app(CheckCompatibilityHandler::class);
    $results = $handler->handle($dto)->getPayload();

    expect($results[6]['status'])->toBe('warning');
    expect($results[6]['code'])->toBe('BIOS_UPDATE_REQUIRED');

    DB::rollBack();
});

test('memory speed rule validates speed and warns downclock', function () {
    DB::beginTransaction();

    $ram = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'RAM-'.uniqid()]);
    setProductAttribute($ram->id, 'ram_speed', 6000);

    $motherboard = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'MB-'.uniqid()]);
    setProductAttribute($motherboard->id, 'motherboard_max_ram_speed', 5200);

    $dto = new CheckCompatibilityDto([
        'ram' => $ram->id,
        'motherboard' => $motherboard->id,
    ]);

    $handler = app(CheckCompatibilityHandler::class);
    $results = $handler->handle($dto)->getPayload();

    expect($results[7]['status'])->toBe('warning');
    expect($results[7]['code'])->toBe('RAM_SPEED_DOWNCLOCK');

    DB::rollBack();
});

test('memory capacity rule validates total limit', function () {
    DB::beginTransaction();

    $ram = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'RAM-'.uniqid()]);
    setProductAttribute($ram->id, 'ram_capacity', 64);

    $motherboard = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'MB-'.uniqid()]);
    setProductAttribute($motherboard->id, 'motherboard_max_ram_capacity', 32); // Max 32GB

    $dto = new CheckCompatibilityDto([
        'ram' => $ram->id,
        'motherboard' => $motherboard->id,
    ]);

    $handler = app(CheckCompatibilityHandler::class);
    $results = $handler->handle($dto)->getPayload();

    expect($results[8]['status'])->toBe('incompatible');
    expect($results[8]['code'])->toBe('RAM_CAPACITY_EXCEEDED');

    DB::rollBack();
});

test('memory slots rule validates DIMM module count', function () {
    DB::beginTransaction();

    $ram = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'RAM-'.uniqid()]);
    setProductAttribute($ram->id, 'ram_modules', 4);

    $motherboard = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'MB-'.uniqid()]);
    setProductAttribute($motherboard->id, 'motherboard_ram_slots', 2); // Max 2 slots

    $dto = new CheckCompatibilityDto([
        'ram' => $ram->id,
        'motherboard' => $motherboard->id,
    ]);

    $handler = app(CheckCompatibilityHandler::class);
    $results = $handler->handle($dto)->getPayload();

    expect($results[9]['status'])->toBe('incompatible');
    expect($results[9]['code'])->toBe('RAM_SLOTS_EXCEEDED');

    DB::rollBack();
});

test('ecc compatibility rule validates support', function () {
    DB::beginTransaction();

    $ram = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'RAM-'.uniqid()]);
    setProductAttribute($ram->id, 'ram_is_ecc', 1);

    $cpu = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'CPU-'.uniqid()]);
    setProductAttribute($cpu->id, 'cpu_supports_ecc', 0); // CPU does not support ECC

    $dto = new CheckCompatibilityDto([
        'ram' => $ram->id,
        'cpu' => $cpu->id,
    ]);

    $handler = app(CheckCompatibilityHandler::class);
    $results = $handler->handle($dto)->getPayload();

    expect($results[10]['status'])->toBe('incompatible');
    expect($results[10]['code'])->toBe('ECC_NOT_SUPPORTED_BY_CPU');

    DB::rollBack();
});

test('xmp expo profile rule checks profiles', function () {
    DB::beginTransaction();

    $ram = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'RAM-'.uniqid()]);
    setProductAttribute($ram->id, 'ram_profile', 'EXPO');

    $motherboard = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'MB-'.uniqid()]);
    setProductAttribute($motherboard->id, 'supported_ram_profiles', 'XMP'); // EXPO not supported

    $dto = new CheckCompatibilityDto([
        'ram' => $ram->id,
        'motherboard' => $motherboard->id,
    ]);

    $handler = app(CheckCompatibilityHandler::class);
    $results = $handler->handle($dto)->getPayload();

    expect($results[11]['status'])->toBe('warning');
    expect($results[11]['code'])->toBe('RAM_PROFILE_MISMATCH');

    DB::rollBack();
});

test('pcie generation and lanes capacity rules check slot limitations', function () {
    DB::beginTransaction();

    $gpu = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'GPU-'.uniqid()]);
    setProductAttribute($gpu->id, 'gpu_pcie_gen', 4);
    setProductAttribute($gpu->id, 'pcie_lanes', 16);

    $motherboard = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'MB-'.uniqid()]);
    setProductAttribute($motherboard->id, 'motherboard_pcie_gen', 3);
    setProductAttribute($motherboard->id, 'motherboard_pcie_lanes', 8); // Only 8 lanes available

    $dto = new CheckCompatibilityDto([
        'gpu' => $gpu->id,
        'motherboard' => $motherboard->id,
    ]);

    $handler = app(CheckCompatibilityHandler::class);
    $results = $handler->handle($dto)->getPayload();

    expect($results[12]['status'])->toBe('warning');
    expect($results[12]['code'])->toBe('PCIE_GEN_DOWNGRADE');

    expect($results[13]['status'])->toBe('incompatible');
    expect($results[13]['code'])->toBe('PCIE_LANES_EXCEEDED');

    DB::rollBack();
});

test('gpu thickness rule checks slot availability', function () {
    DB::beginTransaction();

    $gpu = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'GPU-'.uniqid()]);
    setProductAttribute($gpu->id, 'gpu_slots', 3.5);

    $case = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'CASE-'.uniqid()]);
    setProductAttribute($case->id, 'case_expansion_slots', 2); // Max 2 slots

    $dto = new CheckCompatibilityDto([
        'gpu' => $gpu->id,
        'case' => $case->id,
    ]);

    $handler = app(CheckCompatibilityHandler::class);
    $results = $handler->handle($dto)->getPayload();

    expect($results[14]['status'])->toBe('incompatible');
    expect($results[14]['code'])->toBe('GPU_TOO_THICK');

    DB::rollBack();
});

test('case clearance rule checks psu length limits', function () {
    DB::beginTransaction();

    $psu = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'PSU-'.uniqid()]);
    setProductAttribute($psu->id, 'psu_length', 180);

    $case = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'CASE-'.uniqid()]);
    setProductAttribute($case->id, 'case_max_psu_length', 160); // Max 160mm

    $dto = new CheckCompatibilityDto([
        'psu' => $psu->id,
        'case' => $case->id,
    ]);

    $handler = app(CheckCompatibilityHandler::class);
    $results = $handler->handle($dto)->getPayload();

    expect($results[15]['status'])->toBe('incompatible');
    expect($results[15]['code'])->toBe('PSU_TOO_LONG');

    DB::rollBack();
});

test('psu connector capacity and type checks validation rules', function () {
    DB::beginTransaction();

    $gpu = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'GPU-'.uniqid()]);
    setProductAttribute($gpu->id, 'gpu_pcie_connectors_required', 3);

    $psu = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'PSU-'.uniqid()]);
    setProductAttribute($psu->id, 'psu_pcie_connectors_available', 2);

    $dto = new CheckCompatibilityDto([
        'gpu' => $gpu->id,
        'psu' => $psu->id,
    ]);

    $handler = app(CheckCompatibilityHandler::class);
    $results = $handler->handle($dto)->getPayload();

    expect($results[16]['status'])->toBe('incompatible');
    expect($results[16]['code'])->toBe('INSUFFICIENT_PSU_CONNECTORS');

    DB::rollBack();
});

test('storage interface drive validates m.2 slots availability', function () {
    DB::beginTransaction();

    $storage1 = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'SSD-1']);
    setProductAttribute($storage1->id, 'storage_interface', 'M.2 NVMe');

    $storage2 = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'SSD-2']);
    setProductAttribute($storage2->id, 'storage_interface', 'M.2 NVMe');

    $motherboard = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'MB-'.uniqid()]);
    setProductAttribute($motherboard->id, 'motherboard_m2_slots', 1); // Only 1 M.2 slot

    $dto = new CheckCompatibilityDto([
        'storage1' => $storage1->id,
        'storage2' => $storage2->id,
        'motherboard' => $motherboard->id,
    ]);

    $handler = app(CheckCompatibilityHandler::class);
    $results = $handler->handle($dto)->getPayload();

    expect($results[17]['status'])->toBe('incompatible');
    expect($results[17]['code'])->toBe('INSUFFICIENT_M2_SLOTS');

    DB::rollBack();
});

test('nvme gen checks motherboard nvme generation capability', function () {
    DB::beginTransaction();

    $storage = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'SSD-1']);
    setProductAttribute($storage->id, 'storage_interface', 'M.2 NVMe');
    setProductAttribute($storage->id, 'storage_pcie_gen', 4);

    $motherboard = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'MB-'.uniqid()]);
    setProductAttribute($motherboard->id, 'motherboard_m2_pcie_gen', 3); // Capped at PCIe Gen 3

    $dto = new CheckCompatibilityDto([
        'storage' => $storage->id,
        'motherboard' => $motherboard->id,
    ]);

    $handler = app(CheckCompatibilityHandler::class);
    $results = $handler->handle($dto)->getPayload();

    expect($results[18]['status'])->toBe('warning');
    expect($results[18]['code'])->toBe('NVME_GEN_DOWNGRADE');

    DB::rollBack();
});

test('m2 lane sharing warns when both ports populated', function () {
    DB::beginTransaction();

    $m2 = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'SSD-M2']);
    setProductAttribute($m2->id, 'storage_interface', 'M.2 NVMe');

    $sata = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'HDD-SATA']);
    setProductAttribute($sata->id, 'storage_interface', 'SATA');

    $motherboard = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'MB-'.uniqid()]);
    setProductAttribute($motherboard->id, 'motherboard_shares_lanes', 1); // Shared lane configuration

    $dto = new CheckCompatibilityDto([
        'm2' => $m2->id,
        'sata' => $sata->id,
        'motherboard' => $motherboard->id,
    ]);

    $handler = app(CheckCompatibilityHandler::class);
    $results = $handler->handle($dto)->getPayload();

    expect($results[19]['status'])->toBe('warning');
    expect($results[19]['code'])->toBe('M2_LANE_SHARING_ACTIVE');

    DB::rollBack();
});

test('chassis usb audio argb and fan headers validate requirements', function () {
    DB::beginTransaction();

    $case = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'CASE-'.uniqid()]);
    setProductAttribute($case->id, 'case_requires_usb3', 1);
    setProductAttribute($case->id, 'case_requires_typec', 1);
    setProductAttribute($case->id, 'case_audio_type', 'AC97');
    setProductAttribute($case->id, 'rgb_type', '5V ARGB');
    setProductAttribute($case->id, 'fan_count', 5);

    $motherboard = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'MB-'.uniqid()]);
    setProductAttribute($motherboard->id, 'motherboard_usb3_headers', 0);
    setProductAttribute($motherboard->id, 'motherboard_typec_headers', 0);
    setProductAttribute($motherboard->id, 'motherboard_audio_headers', 'HD Audio');
    setProductAttribute($motherboard->id, 'motherboard_5v_argb_headers', 0);
    setProductAttribute($motherboard->id, 'motherboard_fan_headers', 3);

    $dto = new CheckCompatibilityDto([
        'case' => $case->id,
        'motherboard' => $motherboard->id,
    ]);

    $handler = app(CheckCompatibilityHandler::class);
    $results = $handler->handle($dto)->getPayload();

    expect($results[20]['status'])->toBe('warning');
    expect($results[20]['code'])->toBe('MISSING_USB3_HEADER');

    expect($results[21]['status'])->toBe('warning');
    expect($results[21]['code'])->toBe('AUDIO_HEADER_MISMATCH');

    expect($results[22]['status'])->toBe('warning');
    expect($results[22]['code'])->toBe('MISSING_ARGB_HEADER');

    expect($results[23]['status'])->toBe('warning');
    expect($results[23]['code'])->toBe('INSUFFICIENT_FAN_HEADERS');

    DB::rollBack();
});

test('motherboard size form factor matches case dimensions constraints', function () {
    DB::beginTransaction();

    $motherboard = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'MB-'.uniqid()]);
    setProductAttribute($motherboard->id, 'motherboard_form_factor', 'ATX');

    $case = ProductProxy::create(['type' => 'simple', 'attribute_family_id' => 1, 'sku' => 'CASE-'.uniqid()]);
    setProductAttribute($case->id, 'case_supported_form_factors', 'Micro-ATX,Mini-ITX'); // ATX not supported

    $dto = new CheckCompatibilityDto([
        'motherboard' => $motherboard->id,
        'case' => $case->id,
    ]);

    $handler = app(CheckCompatibilityHandler::class);
    $results = $handler->handle($dto)->getPayload();

    expect($results[24]['status'])->toBe('incompatible');
    expect($results[24]['code'])->toBe('FORM_FACTOR_MISMATCH');

    DB::rollBack();
});
