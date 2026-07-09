<?php

declare(strict_types=1);

use App\Foundation\Audit\AuditLogger;
use App\Foundation\Configuration\EnterpriseConfig;
use App\Foundation\Exceptions\BusinessException;
use App\Foundation\Helpers\FoundationHelper;
use App\Foundation\Localization\LocalizationService;
use App\Foundation\Media\MediaService;
use App\Foundation\SEO\SEOService;

test('enterprise config retrieves typed values', function () {
    $config = new EnterpriseConfig;
    expect($config->getString('app.timezone', 'UTC'))->toBeString();
    expect($config->getBool('app.debug', false))->toBeBool();
    expect($config->getInt('session.lifetime', 120))->toBeInt();
    expect($config->getArray('app.providers', []))->toBeArray();
});

test('audit logger generates unique correlation id', function () {
    $logger1 = new AuditLogger;
    $logger2 = new AuditLogger;
    expect($logger1->getCorrelationId())->not->toBe($logger2->getCorrelationId());
    expect(strlen($logger1->getCorrelationId()))->toBe(36); // UUID length
});

test('media service resolves image variants', function () {
    $media = new MediaService;
    $original = 'images/products/laptop.jpg';
    $variant = $media->getVariantPath($original, 'thumb');
    expect($variant)->toBe('images/products/laptop_thumb.jpg');
});

test('seo service resolves custom titles and descriptors', function () {
    $seo = new SEOService;
    expect($seo->resolveTitle('Gaming Laptop'))->toBe('Gaming Laptop | fstio | Fast Technologies');
    expect($seo->resolveDescription(''))->toContain('Fast Technologies');
});

test('localization service formats price with BD taka', function () {
    $localization = new LocalizationService;
    expect($localization->formatPrice(12500.50))->toBe('৳12,500.50');
    expect($localization->getBangladeshDivisions())->toBeArray()->toContain('Dhaka');
});

test('business exceptions throw standard error codes', function () {
    $exception = BusinessException::invalidInput('Custom Error');
    expect($exception->getMessage())->toBe('Custom Error');
    expect($exception->getCode())->toBe(422);
});

test('foundation helpers format bangladesh phones correctly', function () {
    expect(FoundationHelper::formatBangladeshPhone('01759190782'))->toBe('01759190782');
    expect(FoundationHelper::formatBangladeshPhone('+8801759190782'))->toBe('01759190782');
    expect(FoundationHelper::formatBangladeshPhone('8801759190782'))->toBe('01759190782');
    expect(FoundationHelper::formatMoney(1500))->toBe('৳1,500.00');
    expect(FoundationHelper::slugify('Asus ROG Strix'))->toBe('asus-rog-strix');
});
