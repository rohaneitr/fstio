<?php

namespace App\Providers;

use App\Domain\Contracts\ProductInventoryPortInterface;
use App\Domain\Contracts\ProductPricingPortInterface;
use App\Foundation\Providers\FoundationServiceProvider;
use App\Infrastructure\Indexers\EnterpriseBookingIndexer;
use App\Infrastructure\Indexers\EnterpriseDownloadableIndexer;
use App\Infrastructure\Indexers\EnterpriseSimpleIndexer;
use App\Infrastructure\Indexers\EnterpriseVirtualIndexer;
use App\Infrastructure\Pricing\BagistoProductInventoryAdapter;
use App\Infrastructure\Pricing\BagistoProductPricingAdapter;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use Webkul\BookingProduct\Helpers\Indexers\Price\Booking;
use Webkul\Product\Helpers\Indexers\Price\Downloadable;
use Webkul\Product\Helpers\Indexers\Price\Simple;
use Webkul\Product\Helpers\Indexers\Price\Virtual;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->register(FoundationServiceProvider::class);

        $this->app->bind(
            ProductPricingPortInterface::class,
            BagistoProductPricingAdapter::class
        );

        $this->app->bind(
            ProductInventoryPortInterface::class,
            BagistoProductInventoryAdapter::class
        );

        $this->app->bind(
            Simple::class,
            EnterpriseSimpleIndexer::class
        );

        $this->app->bind(
            Virtual::class,
            EnterpriseVirtualIndexer::class
        );

        $this->app->bind(
            Downloadable::class,
            EnterpriseDownloadableIndexer::class
        );

        $this->app->bind(
            Booking::class,
            EnterpriseBookingIndexer::class
        );

        $allowedIPs = array_map('trim', explode(',', config('app.debug_allowed_ips', '')));

        $allowedIPs = array_filter($allowedIPs);

        if (empty($allowedIPs)) {
            return;
        }

        if (in_array(Request::ip(), $allowedIPs)) {
            Debugbar::enable();
        } else {
            Debugbar::disable();
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ParallelTesting::setUpTestDatabase(function (string $database, int $token) {
            Artisan::call('db:seed');
        });
    }
}
