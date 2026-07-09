<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\BrandController;
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
});
