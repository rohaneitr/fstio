<?php

declare(strict_types=1);

use App\Domain\Repositories\ProductInventoryRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Webkul\Inventory\Models\InventorySource;
use Webkul\Product\Models\ProductProxy;
use Webkul\User\Models\Admin;

use function Pest\Laravel\post;

test('unauthorized user cannot adjust inventory quantity', function () {
    $response = post(route('admin.catalog.inventories.adjust'), [
        'product_id' => 1,
        'inventory_source_id' => 1,
        'qty_change' => 5,
    ]);

    $response->assertRedirect('/admin/login');
});

test('authenticated administrator can adjust inventory quantity successfully', function () {
    DB::beginTransaction();

    $admin = Admin::first() ?: Admin::factory()->create();
    $this->actingAs($admin, 'admin');

    $product = ProductProxy::create([
        'type' => 'simple',
        'attribute_family_id' => 1,
        'sku' => 'TEST-INV-SKU-'.uniqid(),
    ]);

    $source = InventorySource::first() ?: InventorySource::create([
        'code' => 'test-source-'.uniqid(),
        'name' => 'Test Source',
        'status' => 1,
        'street' => '123 Main St',
        'country' => 'BD',
        'state' => 'Dhaka',
        'city' => 'Dhaka',
        'postcode' => '1200',
    ]);

    $response = post(route('admin.catalog.inventories.adjust'), [
        'product_id' => $product->id,
        'inventory_source_id' => $source->id,
        'qty_change' => 10,
    ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'message' => 'Inventory adjusted successfully.',
    ]);

    // Assert database values
    $record = app(ProductInventoryRepositoryInterface::class)->findForProductAndSource($product->id, $source->id);
    expect($record)->not->toBeNull();
    expect((int) $record->qty)->toBe(10);

    // Assert audit logs
    $log = DB::table('inventory_audit_logs')
        ->where('product_id', $product->id)
        ->where('inventory_source_id', $source->id)
        ->first();
    expect($log)->not->toBeNull();
    expect((int) $log->qty_change)->toBe(10);
    expect((int) $log->previous_qty)->toBe(0);
    expect((int) $log->new_qty)->toBe(10);

    DB::rollBack();
});

test('inventory adjustment fails if result is negative quantity', function () {
    DB::beginTransaction();

    $admin = Admin::first() ?: Admin::factory()->create();
    $this->actingAs($admin, 'admin');

    $product = ProductProxy::create([
        'type' => 'simple',
        'attribute_family_id' => 1,
        'sku' => 'TEST-INV-NEG-'.uniqid(),
    ]);

    $source = InventorySource::first() ?: InventorySource::create([
        'code' => 'test-source-'.uniqid(),
        'name' => 'Test Source',
        'status' => 1,
        'street' => '123 Main St',
        'country' => 'BD',
        'state' => 'Dhaka',
        'city' => 'Dhaka',
        'postcode' => '1200',
    ]);

    // Try setting negative directly
    $response = post(route('admin.catalog.inventories.adjust'), [
        'product_id' => $product->id,
        'inventory_source_id' => $source->id,
        'qty_change' => -10, // Invariant validation should prevent this
    ]);

    $response->assertStatus(500); // Throws domain validation exception resulting in 500

    DB::rollBack();
});
