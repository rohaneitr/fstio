<?php

declare(strict_types=1);

namespace App\Foundation\Configuration;

use App\Foundation\Contracts\ServiceInterface;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class EnterpriseConfig implements ServiceInterface
{
    /**
     * Get a string configuration value.
     */
    public function getString(string $key, string $default = ''): string
    {
        return (string) Config::get($key, $default);
    }

    /**
     * Get a boolean configuration value.
     */
    public function getBool(string $key, bool $default = false): bool
    {
        return filter_var(Config::get($key, $default), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Get an integer configuration value.
     */
    public function getInt(string $key, int $default = 0): int
    {
        return (int) Config::get($key, $default);
    }

    /**
     * Get an array configuration value.
     *
     * @return array<mixed>
     */
    public function getArray(string $key, array $default = []): array
    {
        return (array) Config::get($key, $default);
    }

    /**
     * Check if a feature flag is enabled.
     */
    public function isFeatureEnabled(string $feature): bool
    {
        return $this->getBool("features.{$feature}", false);
    }

    /**
     * Get environment name.
     */
    public function getEnvironment(): string
    {
        return App::environment();
    }

    /**
     * Get default currency code.
     */
    public function getDefaultCurrency(): string
    {
        return $this->getString('app.currency', 'BDT');
    }

    /**
     * Get support contact number.
     */
    public function getSupportPhone(): string
    {
        return $this->getString('app.support_phone', '+8801759190782');
    }

    /**
     * Get support email.
     */
    public function getSupportEmail(): string
    {
        return $this->getString('app.support_email', 'fctbd1@gmail.com');
    }
}
