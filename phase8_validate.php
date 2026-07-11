<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Application\Contracts\QueryBusInterface;
use App\Domain\Models\Product;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Webkul\Checkout\Facades\Cart;

echo "--- PHASE 8 ENTERPRISE VALIDATION ---\n";

DB::beginTransaction();

try {
    $product = Product::where('type', 'simple')->first();
    if (! $product) {
        throw new Exception('No product found.');
    }

    // 1. Force scarcity condition to check overlay
    DB::table('product_inventories')->where('product_id', $product->id)->delete();
    DB::table('product_inventories')->insert([
        'product_id' => $product->id,
        'inventory_source_id' => 1,
        'qty' => 3,
    ]);

    // 2. Setup counters for Idempotency and Deduplication Checks
    global $queryBusCount;
    $queryBusCount = 0;

    // We can't spy easily without mockery, but we can listen to events
    $executedEvents = [];
    Event::listen('*', function ($eventName, $data) use (&$executedEvents) {
        if (str_starts_with($eventName, 'checkout.cart.collect.totals')) {
            $executedEvents[$eventName] = ($executedEvents[$eventName] ?? 0) + 1;
        }
    });

    // We will bind a decorator to QueryBus to count calls
    $originalBus = app(QueryBusInterface::class);
    app()->instance(QueryBusInterface::class, new class($originalBus) implements QueryBusInterface
    {
        public $bus;

        public function __construct($bus)
        {
            $this->bus = $bus;
        }

        public function ask(object $query): mixed
        {
            global $queryBusCount;
            $queryBusCount++;

            return $this->bus->ask($query);
        }
    });

    // 3. Clear cart and add product
    Cart::deActivateCart();
    $cart = Cart::addProduct($product, [
        'product_id' => $product->id,
        'quantity' => 1,
        'is_configurable' => false,
    ]);

    if (! $cart) {
        throw new Exception('Failed to add product to cart.');
    }

    // 4. Concurrency & Idempotency - collectTotals multiple times
    Cart::collectTotals();
    Cart::collectTotals();
    Cart::collectTotals();

    $cart = Cart::getCart();
    $item = $cart->items->first();

    echo "Cart ID: {$cart->id}\n";
    echo "Item ID: {$item->id}\n";
    echo "Base Price: {$item->base_price}\n";
    echo "Custom Price: {$item->custom_price}\n";
    echo "Total: {$item->total}\n";
    echo "Grand Total: {$cart->grand_total}\n";
    echo "QueryBus Executions: $queryBusCount\n";
    echo "Collect Totals Events:\n";
    print_r($executedEvents);

    // 5. Detect cart drift
    $expectedPrice = round($product->getTypeInstance()->getFinalPrice(1) * 1.10, 2);
    if ((float) $item->custom_price !== (float) $expectedPrice) {
        throw new Exception("Price drift detected! Expected: $expectedPrice, Got: {$item->custom_price}");
    }

    // 6. Idempotency Check
    $expectedCount = $executedEvents['checkout.cart.collect.totals.before'] ?? 0;
    if ($queryBusCount !== $expectedCount) {
        throw new Exception("Duplicate execution detected! QueryBus fired $queryBusCount times, expected $expectedCount.");
    }

    echo "Validation Successful.\n";

} catch (Throwable $e) {
    echo 'BLOCKED: '.$e->getMessage()."\n".$e->getTraceAsString()."\n";
} finally {
    DB::rollBack();
}
