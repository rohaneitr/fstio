<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\BuildController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\RecommendationController;
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
