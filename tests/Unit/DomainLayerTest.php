<?php

declare(strict_types=1);

use App\Domain\Enums\CompatibilityType;
use App\Domain\Enums\SerialStatus;
use App\Domain\Enums\WarrantyType;
use App\Domain\Exceptions\InvalidBrandException;
use App\Domain\Exceptions\InvalidMoneyException;
use App\Domain\Exceptions\InvalidPhoneException;
use App\Domain\Exceptions\InvalidSerialException;
use App\Domain\Exceptions\InvalidWarrantyException;
use App\Domain\Factories\BrandFactory;
use App\Domain\Factories\CompatibilityFactory;
use App\Domain\Factories\ProductSerialFactory;
use App\Domain\Services\CompatibilityEngine;
use App\Domain\Services\PriceCalculator;
use App\Domain\Services\SlugGenerator;
use App\Domain\Services\WarrantyPolicy;
use App\Domain\Specifications\BrandSpecification;
use App\Domain\Specifications\CompatibilitySpecification;
use App\Domain\Specifications\WarrantySpecification;
use App\Domain\ValueObjects\Currency;
use App\Domain\ValueObjects\Dimension;
use App\Domain\ValueObjects\Money;
use App\Domain\ValueObjects\PartNumber;
use App\Domain\ValueObjects\Phone;
use App\Domain\ValueObjects\ProductCode;
use App\Domain\ValueObjects\SeoMeta;
use App\Domain\ValueObjects\SerialNumber;
use App\Domain\ValueObjects\Slug;
use App\Domain\ValueObjects\Warranty;
use App\Domain\ValueObjects\Weight;

test('money value object validates calculations and handles exceptions', function () {
    $m1 = Money::BDT(100);
    $m2 = Money::BDT(200);
    expect($m1->add($m2)->getDecimalAmount())->toBe(300.0);
    expect($m2->subtract($m1)->getDecimalAmount())->toBe(100.0);

    $m3 = Money::fromSubunits(15025, Currency::BDT());
    expect($m3->multiply(2)->getDecimalAmount())->toBe(300.50);

    $usd = new Currency('USD', '$');
    $mUsd = Money::fromSubunits(1000, $usd);

    $this->expectException(InvalidMoneyException::class);
    $m1->add($mUsd);
});

test('phone value object checks operator layouts and triggers exceptions', function () {
    $phone = new Phone('+8801759190782');
    expect($phone->getNormalized())->toBe('01759190782');
    expect($phone->getFormatted())->toBe('+880 1759-190782');

    $this->expectException(InvalidPhoneException::class);
    new Phone('01234567890'); // Invalid operator digit
});

test('serial number enforces rules and exceptions', function () {
    $serial = new SerialNumber('ABC-12345');
    expect($serial->getValue())->toBe('ABC-12345');

    $this->expectException(InvalidSerialException::class);
    new SerialNumber('A'); // Too short
});

test('slug value object strictly checks urls', function () {
    $slug = new Slug('asus-gaming-pc');
    expect($slug->getValue())->toBe('asus-gaming-pc');

    $this->expectException(InvalidBrandException::class);
    new Slug('Asus_Gaming_PC'); // Invalid chars (uppercase / underscore)
});

test('seo meta wraps variables', function () {
    $seo = new SeoMeta('Title', 'Desc');
    expect($seo->getTitle())->toBe('Title');
    expect($seo->getDescription())->toBe('Desc');
});

test('warranty period wraps validation and format rules', function () {
    $warranty = new Warranty(24, WarrantyType::OFFICIAL);
    expect($warranty->getMonths())->toBe(24);
    expect($warranty->getFormatted())->toBe('2 Years (Official Warranty)');

    $warranty2 = new Warranty(15, WarrantyType::UNOFFICIAL);
    expect($warranty2->getFormatted())->toBe('1 Year 3 Months (Unofficial Warranty)');

    $this->expectException(InvalidWarrantyException::class);
    new Warranty(130, WarrantyType::DEALER); // Over 120 limit
});

test('product code values enforce alphanumeric structures', function () {
    $code = new ProductCode('ASUS-102');
    expect($code->getSku())->toBe('ASUS-102');

    $this->expectException(InvalidArgumentException::class);
    new ProductCode('A');
});

test('part number matches manufacturer attributes', function () {
    $pn = new PartNumber('ROG/STRIX/V2');
    expect($pn->getValue())->toBe('ROG/STRIX/V2');

    $this->expectException(InvalidArgumentException::class);
    new PartNumber('A');
});

test('dimension wraps height width depth volume metrics', function () {
    $dim = new Dimension(100, 200, 300);
    expect($dim->getVolume())->toBe(6000000.0);

    $this->expectException(InvalidArgumentException::class);
    new Dimension(0, 10, 10);
});

test('weight maps metric scales and gram converters', function () {
    $w1 = new Weight(1.5, 'kg');
    expect($w1->toGrams())->toBe(1500.0);

    $w2 = new Weight(500, 'g');
    expect($w2->toGrams())->toBe(500.0);

    $this->expectException(InvalidArgumentException::class);
    new Weight(1, 'ton');
});

test('factories resolve entities successfully', function () {
    $slugGen = new SlugGenerator;
    $slug = $slugGen->generate('Intel Core i9');

    $brandFact = new BrandFactory;
    $brand = $brandFact->create($slug, 'logo.jpg');
    expect($brand->slug)->toBe('intel-core-i9');

    $compatFact = new CompatibilityFactory;
    $rule = $compatFact->create(10, 20, CompatibilityType::SOCKET);
    expect($rule->rule_type)->toBe('socket');

    $serialFact = new ProductSerialFactory;
    $serialObj = new SerialNumber('SN-777');
    $serial = $serialFact->create(5, 1, $serialObj, SerialStatus::RESERVED);
    expect($serial->serial_number)->toBe('SN-777');
    expect($serial->status)->toBe('reserved');
});

test('specifications assert invariants properly', function () {
    $brandFact = new BrandFactory;
    $brandSpec = new BrandSpecification;
    $slug = new Slug('gigabyte');
    $brand = $brandFact->create($slug);
    expect($brandSpec->isSatisfiedBy($brand))->toBeTrue();

    $compatFact = new CompatibilityFactory;
    $ruleSpec = new CompatibilitySpecification;
    $ruleOk = $compatFact->create(1, 2, CompatibilityType::INTERFACE);
    $ruleBad = $compatFact->create(1, 1, CompatibilityType::INTERFACE);
    expect($ruleSpec->isSatisfiedBy($ruleOk))->toBeTrue();
    expect($ruleSpec->isSatisfiedBy($ruleBad))->toBeFalse();

    $warrantySpec = new WarrantySpecification;
    $w1 = new Warranty(36, WarrantyType::DEALER);
    expect($warrantySpec->isSatisfiedBy($w1))->toBeTrue();
});

test('domain services process aggregates logic', function () {
    $ruleSpec = new CompatibilitySpecification;
    $engine = new CompatibilityEngine($ruleSpec);
    $compatFact = new CompatibilityFactory;
    $rule = $compatFact->create(1, 2, CompatibilityType::SOCKET);
    expect($engine->checkCompatible($rule))->toBeTrue();

    $policy = new WarrantyPolicy;
    $w1 = new Warranty(12, WarrantyType::OFFICIAL);
    $w2 = new Warranty(12, WarrantyType::UNOFFICIAL);
    expect($policy->isExtendable($w1))->toBeTrue();
    expect($policy->isExtendable($w2))->toBeFalse();

    $calc = new PriceCalculator;
    $total = $calc->calculateTotal(Money::BDT(100), 0.05);
    expect($total->getDecimalAmount())->toBe(105.0);
});
