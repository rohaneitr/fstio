<?php

declare(strict_types=1);

uses(TestCase::class);

use App\Foundation\Address\Address;
use App\Foundation\Geography\Division;
use App\Foundation\Geography\GeoResolver;
use App\Foundation\Money\Money;
use App\Foundation\Money\MoneyFormatter;
use App\Foundation\Phone\BangladeshPhone;
use App\Foundation\Tax\TaxResolverInterface;
use Tests\TestCase;

test('money module executes precise subunit arithmetic', function () {
    $price1 = Money::BDT(1500.50);
    $price2 = Money::BDT(2500.25);

    $total = $price1->add($price2);
    expect($total->getAmount())->toBe(400075); // 4000.75 in paisa
    expect($total->getDecimalAmount())->toBe(4000.75);

    $diff = $price2->subtract($price1);
    expect($diff->getDecimalAmount())->toBe(999.75);

    $tax = $price1->multiply(0.05); // 5% VAT
    expect($tax->getDecimalAmount())->toBe(75.03); // Rounded 75.025 to 75.03

    $formatter = new MoneyFormatter;
    expect($formatter->format($price1))->toBe('৳1,500.50');
});

test('bangladesh phone value object parses and formats mobile numbers', function () {
    $phone = new BangladeshPhone('+8801759190782');
    expect($phone->getNormalized())->toBe('01759190782');
    expect($phone->getFormatted())->toBe('+880 1759-190782');

    $phone2 = new BangladeshPhone('01912345678');
    expect($phone2->getNormalized())->toBe('01912345678');

    $this->expectException(InvalidArgumentException::class);
    new BangladeshPhone('1234567890'); // Invalid length / prefix
});

test('georesolver validates division district and upazila relationships', function () {
    $resolver = new GeoResolver;

    expect($resolver->validateGeo('Dhaka', 'Dhaka', 'Gulshan'))->toBeTrue();
    expect($resolver->validateGeo('Dhaka', 'Chattogram', 'Panchlaish'))->toBeFalse(); // Cross division
    expect($resolver->validateGeo('Rajshahi', 'Dhaka', 'Savar'))->toBeFalse(); // Invalid division
});

test('address formatter maps structured address schemas', function () {
    $address = new Address(
        'Multiplan Center',
        'Dhanmondi',
        'Dhaka',
        Division::DHAKA,
        '1205'
    );

    expect($address->getFormatted())->toBe('Multiplan Center, Dhanmondi, Dhaka, Dhaka - 1205');
});

test('tax resolver calculates standard vat rate', function () {
    $resolver = app(TaxResolverInterface::class);
    $price = Money::BDT(1000);
    $vat = $resolver->calculateTax($price, 0.05);

    expect($vat->getDecimalAmount())->toBe(50.0);
});
