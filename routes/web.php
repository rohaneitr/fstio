<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\BuildController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\RecommendationController;
use App\Http\Controllers\PCBuilderController;
use Illuminate\Support\Facades\Route;
use Webkul\Core\Http\Middleware\NoCacheMiddleware;

Route::group(['middleware' => ['web', 'admin', NoCacheMiddleware::class], 'prefix' => config('app.admin_url')], function () {
    Route::prefix('catalog/brands')->group(function () {
        Route::get('', [BrandController::class, 'index'])->name('admin.catalog.brands.index');
        Route::get('create', [BrandController::class, 'create'])->name('admin.catalog.brands.create');
        Route::post('create', [BrandController::class, 'store'])->name('admin.catalog.brands.store');
        Route::get('edit/{id}', [BrandController::class, 'edit'])->name('admin.catalog.brands.edit');
        Route::put('edit/{id}', [BrandController::class, 'update'])->name('admin.catalog.brands.update');
        Route::delete('edit/{id}', [BrandController::class, 'destroy'])->name('admin.catalog.brands.delete');
        Route::post('mass-delete', [BrandController::class, 'massDestroy'])->name('admin.catalog.brands.mass_delete');
    });

    Route::post('catalog/inventories/adjust', [InventoryController::class, 'store'])->name('admin.catalog.inventories.adjust');

    Route::get('catalog/recommendations/{id}', [RecommendationController::class, 'index'])->name('admin.catalog.products.recommendations');

    Route::prefix('catalog/builds')->group(function () {
        Route::post('', [BuildController::class, 'store'])->name('admin.catalog.builds.store');
        Route::post('clone/{id}', [BuildController::class, 'clone'])->name('admin.catalog.builds.clone');
        Route::delete('{id}', [BuildController::class, 'destroy'])->name('admin.catalog.builds.delete');
        Route::post('cart/{id}', [BuildController::class, 'addToCart'])->name('admin.catalog.builds.cart');
    });
});

Route::group(['middleware' => ['web']], function () {
    Route::get('pc-builder', [PCBuilderController::class, 'index'])->name('shop.pc-builder.index');
    Route::get('pc-builder/{uuid}', [PCBuilderController::class, 'index'])->name('shop.pc-builder.show');

    Route::prefix('api/pc-builder')->group(function () {
        Route::get('products', [PCBuilderController::class, 'products'])->name('shop.pc-builder.api.products');
        Route::post('compatibility', [PCBuilderController::class, 'compatibility'])->name('shop.pc-builder.api.compatibility');
        Route::post('save', [PCBuilderController::class, 'save'])->name('shop.pc-builder.api.save');
        Route::post('clone/{id}', [PCBuilderController::class, 'clone'])->name('shop.pc-builder.api.clone');
        Route::delete('{id}', [PCBuilderController::class, 'destroy'])->name('shop.pc-builder.api.delete');
        Route::post('cart/{id}', [PCBuilderController::class, 'addToCart'])->name('shop.pc-builder.api.cart');
        Route::get('recommendations/{productId}', [PCBuilderController::class, 'recommendations'])->name('shop.pc-builder.api.recommendations');
    });
});
