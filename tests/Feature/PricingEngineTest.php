<?php

declare(strict_types=1);

use App\Application\Contracts\QueryBusInterface;
use App\Application\DTO\CalculatePriceDto;
use App\Application\Queries\GetProductPriceQuery;
use App\Domain\Models\Product;
use App\Domain\ValueObjects\Money;
use Illuminate\Support\Facades\DB;
use Webkul\Product\Repositories\ProductRepository;

test('money value object calculations maintain decimal precision', function () {
    $price1 = Money::BDT(19.99);
    $price2 = Money::BDT(25.01);

    $sum = $price1->add($price2);
    expect($sum->getDecimalAmount())->toBe(45.0);
    expect($sum->getAmount())->toBe(4500);

    $vat = $price1->multiply(0.05); // 0.9995 rounded to 1.00
    expect($vat->getDecimalAmount())->toBe(1.0);
});

test('query bus resolves GetProductPriceQuery successfully', function () {
    DB::beginTransaction();

    // Fetch or create an existing product
    $productRepo = app(ProductRepository::class);
    $product = Product::first();

    if ($product) {
        $dto = new CalculatePriceDto($product->id, 1);
        $query = new GetProductPriceQuery($dto);

        $queryBus = app(QueryBusInterface::class);
        $priceMoney = $queryBus->ask($query);

        expect($priceMoney)->toBeInstanceOf(Money::class);
        expect($priceMoney->getDecimalAmount())->toBeGreaterThan(0);
    }

    DB::rollBack();
});

test('inventory dynamic overlay scarcity markup applies to low stock items', function () {
    DB::beginTransaction();

    $product = Product::first();
    if ($product) {
        // Set stock to 3 items
        DB::table('product_inventories')
            ->where('product_id', $product->id)
            ->delete();

        DB::table('product_inventories')->insert([
            'product_id' => $product->id,
            'inventory_source_id' => 1,
            'qty' => 3,
        ]);

        $basePrice = (float) $product->getTypeInstance()->getFinalPrice(1);
        $expectedOverlayPrice = $basePrice * 1.10;

        // Retrieve price via query bus
        $queryBus = app(QueryBusInterface::class);
        $priceMoney = $queryBus->ask(
            new GetProductPriceQuery(
                new CalculatePriceDto($product->id, 1)
            )
        );

        expect($priceMoney->getDecimalAmount())->toBe(round($expectedOverlayPrice, 2));

        // Retrieve model attribute directly - should return unpolluted base price
        $freshProduct = Product::find($product->id);
        expect($freshProduct->price)->toBe(round($basePrice, 2));
    }

    DB::rollBack();
});

test('price calculator returns base price without overlay if stock is high', function () {
    DB::beginTransaction();

    $product = Product::first();
    if ($product) {
        // Set stock to 20 items
        DB::table('product_inventories')
            ->where('product_id', $product->id)
            ->delete();

        DB::table('product_inventories')->insert([
            'product_id' => $product->id,
            'inventory_source_id' => 1,
            'qty' => 20,
        ]);

        $basePrice = (float) $product->getTypeInstance()->getFinalPrice(1);

        // Retrieve price via query bus
        $queryBus = app(QueryBusInterface::class);
        $priceMoney = $queryBus->ask(
            new GetProductPriceQuery(
                new CalculatePriceDto($product->id, 1)
            )
        );

        expect($priceMoney->getDecimalAmount())->toBe(round($basePrice, 2));
    }

    DB::rollBack();
});
