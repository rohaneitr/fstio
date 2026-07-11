<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Domain\Models\Product;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Event;
use Webkul\Checkout\Facades\Cart;

// 1. Create a dummy cart and product instance
$product = Product::first();
if (! $product) {
    echo "No product found\n";
    exit(1);
}

try {
    // We can't easily fake the entire session checkout flow here without db changes or session mock.
    // Instead, we will simulate the exact event emission that Cart.php does.

    // Mock the Cart item
    $cart = new stdClass;
    $cart->items = collect([
        (object) [
            'id' => 1,
            'product_id' => $product->id,
            'quantity' => 1,
            'custom_price' => null,
            'price' => 500,
            'base_price' => 500,
            'total' => 500,
            'base_total' => 500,
            'save' => function () {
                echo "Item saved\n";
            },
        ],
    ]);

    // Dispatch the exact event
    Event::dispatch('checkout.cart.collect.totals.before', $cart);

    // Inspect the mutated item
    $item = $cart->items->first();
    echo 'Overlay Price Result: '.$item->custom_price."\n";

} catch (Exception $e) {
    echo 'Exception: '.$e->getMessage()."\n";
    exit(1);
}
