<?php

declare(strict_types=1);

namespace App\Foundation\Providers;

use App\Application\Authorization\AuthorizerInterface;
use App\Application\Authorization\SimpleAuthorizer;
use App\Domain\Repositories\BrandRepositoryInterface;
use App\Domain\Repositories\BrandSeriesRepositoryInterface;
use App\Domain\Repositories\CompatibilityRepositoryInterface;
use App\Domain\Repositories\DistrictRepositoryInterface;
use App\Domain\Repositories\Eloquent\BrandRepository;
use App\Domain\Repositories\Eloquent\BrandSeriesRepository;
use App\Domain\Repositories\Eloquent\CompatibilityRepository;
use App\Domain\Repositories\Eloquent\DistrictRepository;
use App\Domain\Repositories\Eloquent\ProductSerialRepository;
use App\Domain\Repositories\Eloquent\UpazilaRepository;
use App\Domain\Repositories\ProductSerialRepositoryInterface;
use App\Domain\Repositories\UpazilaRepositoryInterface;
use App\Foundation\Audit\AuditLogger;
use App\Foundation\Configuration\EnterpriseConfig;
use App\Foundation\Geography\GeoResolver;
use App\Foundation\Localization\LocalizationService;
use App\Foundation\Media\MediaService;
use App\Foundation\SEO\SEOService;
use App\Foundation\Tax\DefaultTaxResolver;
use App\Foundation\Tax\TaxResolverInterface;
use Illuminate\Support\ServiceProvider;

class FoundationServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(EnterpriseConfig::class, function () {
            return new EnterpriseConfig;
        });

        $this->app->singleton(AuditLogger::class, function () {
            return new AuditLogger;
        });

        $this->app->singleton(MediaService::class, function () {
            return new MediaService;
        });

        $this->app->singleton(SEOService::class, function () {
            return new SEOService;
        });

        $this->app->singleton(LocalizationService::class, function () {
            return new LocalizationService;
        });

        $this->app->singleton(GeoResolver::class, function () {
            return new GeoResolver;
        });

        $this->app->bind(TaxResolverInterface::class, DefaultTaxResolver::class);

        // Domain Repositories
        $this->app->bind(BrandRepositoryInterface::class, BrandRepository::class);
        $this->app->bind(BrandSeriesRepositoryInterface::class, BrandSeriesRepository::class);
        $this->app->bind(CompatibilityRepositoryInterface::class, CompatibilityRepository::class);
        $this->app->bind(ProductSerialRepositoryInterface::class, ProductSerialRepository::class);
        $this->app->bind(DistrictRepositoryInterface::class, DistrictRepository::class);
        $this->app->bind(UpazilaRepositoryInterface::class, UpazilaRepository::class);

        // Application Authorization
        $this->app->singleton(AuthorizerInterface::class, SimpleAuthorizer::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Global helper configs or overrides can be wired here
    }
}
