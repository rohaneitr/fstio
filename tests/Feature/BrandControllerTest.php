<?php

declare(strict_types=1);

use App\Domain\Models\Brand;
use Illuminate\Support\Facades\DB;
use Webkul\User\Models\Admin;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

test('authenticated administrator can access brands index page', function () {
    $admin = Admin::first() ?: Admin::factory()->create();
    $this->actingAs($admin, 'admin');

    get(route('admin.catalog.brands.index'))
        ->assertOk()
        ->assertSee('Brands');
});

test('administrator can create a brand successfully', function () {
    DB::beginTransaction();

    $admin = Admin::first() ?: Admin::factory()->create();
    $this->actingAs($admin, 'admin');

    $data = [
        'name' => 'Test Brand',
        'slug' => 'test-brand-slug',
        'website_url' => 'https://testbrand.com',
        'status' => 1,
        'description' => 'Test description',
        'meta_title' => 'Meta Title',
        'meta_keywords' => 'Meta Keywords',
        'meta_description' => 'Meta Description',
        'sort_order' => 10,
    ];

    post(route('admin.catalog.brands.store'), $data)
        ->assertRedirect(route('admin.catalog.brands.index'));

    $brand = Brand::where('slug', 'test-brand-slug')->first();
    expect($brand)->not->toBeNull();
    expect($brand->name)->toBe('Test Brand');
    expect($brand->website_url)->toBe('https://testbrand.com');

    DB::rollBack();
});

test('brand creation fails if validation constraints are violated', function () {
    DB::beginTransaction();

    $admin = Admin::first() ?: Admin::factory()->create();
    $this->actingAs($admin, 'admin');

    $data = [
        'name' => '', // Required field missing
        'slug' => 'invalid slug format', // Slug format violation
    ];

    post(route('admin.catalog.brands.store'), $data)
        ->assertSessionHasErrors(['name', 'slug']);

    DB::rollBack();
});
