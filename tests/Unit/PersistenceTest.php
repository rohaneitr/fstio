<?php

declare(strict_types=1);

uses(TestCase::class);

use App\Domain\Models\Brand;
use App\Domain\Models\District;
use App\Domain\Repositories\BrandRepositoryInterface;
use App\Domain\Repositories\DistrictRepositoryInterface;
use App\Domain\Repositories\UpazilaRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

test('persistence layer executes crud on brand repository', function () {
    DB::beginTransaction();

    $repository = app(BrandRepositoryInterface::class);

    // 1. Create
    $brand = $repository->create([
        'slug' => 'asus-rog',
        'website_url' => 'https://rog.asus.com',
    ]);

    expect($brand)->toBeInstanceOf(Brand::class);
    expect($brand->slug)->toBe('asus-rog');

    // 2. Read / Find
    $found = $repository->find($brand->id);
    expect($found->website_url)->toBe('https://rog.asus.com');

    // 3. Find By Slug
    $foundBySlug = $repository->findBySlug('asus-rog');
    expect($foundBySlug->id)->toBe($brand->id);

    // 4. Update
    $updated = $repository->update([
        'website_url' => 'https://rog.asus.com/bd',
    ], $brand->id);

    expect($updated->website_url)->toBe('https://rog.asus.com/bd');

    // 5. Delete
    $repository->delete($brand->id);
    expect($repository->find($brand->id))->toBeNull();

    DB::rollBack();
});

test('persistence layer maps district and upazila relationships', function () {
    DB::beginTransaction();

    $districtRepo = app(DistrictRepositoryInterface::class);
    $upazilaRepo = app(UpazilaRepositoryInterface::class);

    // Create district
    $district = $districtRepo->create([
        'division_id' => 1, // Mapped to Dhaka division
        'name' => 'Dhaka',
    ]);

    expect($district)->toBeInstanceOf(District::class);

    // Create upazila
    $upazila = $upazilaRepo->create([
        'district_id' => $district->id,
        'name' => 'Gulshan',
        'postal_code' => '1212',
    ]);

    // Check relationship eager loading
    $districtWithUpazilas = District::with('upazilas')->find($district->id);
    expect($districtWithUpazilas->upazilas->first()->name)->toBe('Gulshan');

    DB::rollBack();
});
