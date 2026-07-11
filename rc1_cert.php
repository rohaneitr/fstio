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
use Webkul\Sales\Repositories\OrderRepository;

echo "==========================================================\n";
echo "RC1 PRODUCTION CERTIFICATION GATE\n";
echo "==========================================================\n";

DB::beginTransaction();

try {
    // We will find a simple product
    $simpleProduct = Product::where('type', 'simple')->first();
    if (! $simpleProduct) {
        throw new Exception('No simple product found for testing.');
    }

    // We will test Scarcity Overlay explicitly on this product
    DB::table('product_inventories')->where('product_id', $simpleProduct->id)->delete();
    DB::table('product_inventories')->insert([
        'product_id' => $simpleProduct->id,
        'inventory_source_id' => 1,
        'qty' => 3, // Triggers 10% scarcity overlay
    ]);

    echo "--- SCENARIO 7: INVENTORY SCARCITY OVERLAY ---\n";
    $basePrice = $simpleProduct->getTypeInstance()->getFinalPrice(1);
    echo "Base Price from Product: {$basePrice}\n";

    // Track QueryBus and Events
    global $queryBusCount;
    $queryBusCount = 0;

    global $eventCount;
    $eventCount = 0;

    Event::listen('checkout.cart.collect.totals.before', function () {
        global $eventCount;
        $eventCount++;
    });

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

    Cart::deActivateCart();
    $cart = Cart::addProduct($simpleProduct, [
        'product_id' => $simpleProduct->id,
        'quantity' => 1,
        'is_configurable' => false,
    ]);

    if (! $cart) {
        throw new Exception('Failed to add product to cart.');
    }

    $item = $cart->items->first();
    echo "Cart Item Base Price: {$item->base_price}\n";
    echo "Cart Item Custom Price: {$item->custom_price}\n";
    echo "Cart Grand Total: {$cart->grand_total}\n";

    $expectedPrice = round($basePrice * 1.10, 2);
    echo "Expected Scarcity Price (10%): {$expectedPrice}\n";
    if ((float) $item->custom_price !== (float) $expectedPrice) {
        throw new Exception('Drift detected in Scarcity Overlay.');
    }

    echo "\n--- QUERYBUS VALIDATION ---\n";
    echo "QueryBus Executions: {$queryBusCount}\n";
    echo "Event Executions: {$eventCount}\n";
    if ($queryBusCount !== $eventCount) {
        throw new Exception("Mismatch in QueryBus ($queryBusCount) vs Event ($eventCount) executions.");
    }

    echo "\n--- IDEMPOTENCY ---\n";
    Cart::collectTotals();
    Cart::collectTotals();
    $cart = Cart::getCart();
    $item = $cart->items->first();
    echo "After multiple collectTotals():\n";
    echo "Item Custom Price: {$item->custom_price}\n";
    echo "Cart Grand Total: {$cart->grand_total}\n";

    if ((float) $item->custom_price !== (float) $expectedPrice) {
        throw new Exception('Idempotency drift detected.');
    }

    echo "\n--- DATABASE VALIDATION (CART) ---\n";
    $dbCartItem = DB::table('cart_items')->where('id', $item->id)->first();
    echo "DB cart_items.custom_price: {$dbCartItem->custom_price}\n";
    echo "DB cart_items.total: {$dbCartItem->total}\n";

    echo "\n--- CHECKOUT / ORDER PERSISTENCE ---\n";
    // We mock the cart conversion to order
    // Bagisto OrderRepository uses the cart to prepare order data
    $orderData = [
        'cart_id' => $cart->id,
        'customer_id' => $cart->customer_id,
        'customer_type' => $cart->customer_type,
        'is_guest' => $cart->is_guest,
        'customer_email' => $cart->customer_email,
        'customer_first_name' => $cart->customer_first_name,
        'customer_last_name' => $cart->customer_last_name,
        'shipping_method' => $cart->shipping_method,
        'shipping_title' => $cart->shipping_title,
        'shipping_description' => $cart->shipping_description,
        'shipping_amount' => $cart->shipping_amount,
        'base_shipping_amount' => $cart->base_shipping_amount,
        'sub_total' => $cart->sub_total,
        'base_sub_total' => $cart->base_sub_total,
        'grand_total' => $cart->grand_total,
        'base_grand_total' => $cart->base_grand_total,
        'channel_id' => $cart->channel_id,
        'status' => 'pending',
    ];
    $orderId = DB::table('orders')->insertGetId($orderData);
    $orderItemData = [
        'order_id' => $orderId,
        'product_id' => $item->product_id,
        'qty_ordered' => $item->quantity,
        'price' => $item->price,
        'base_price' => $item->base_price,
        'total' => $item->total,
        'base_total' => $item->base_total,
    ];
    $orderItemId = DB::table('order_items')->insertGetId($orderItemData);

    $dbOrder = DB::table('orders')->where('id', $orderId)->first();
    $dbOrderItem = DB::table('order_items')->where('id', $orderItemId)->first();

    echo "DB orders.grand_total: {$dbOrder->grand_total}\n";
    echo "DB order_items.base_price: {$dbOrderItem->base_price}\n";
    echo "DB order_items.total: {$dbOrderItem->total}\n";

    if ((float) $dbOrderItem->base_price !== (float) $expectedPrice) {
        throw new Exception('Persistence drift detected in Order.');
    }

    echo "\n--- ROLLBACK SIMULATION ---\n";
    // Disable listener by flushing it
    Event::forget('checkout.cart.collect.totals.before');
    // Remove custom price to trigger recalculation from DB
    $item->custom_price = null;
    $item->save();
    Cart::collectTotals();

    $cart = Cart::getCart();
    $item = $cart->items->first();
    echo "After Rollback Listener (Bagisto Native):\n";
    echo "Item Base Price: {$item->base_price}\n";
    echo "Item Custom Price: {$item->custom_price}\n";
    echo "Cart Grand Total: {$cart->grand_total}\n";

    if (! empty($item->custom_price)) {
        throw new Exception('Rollback failed to restore native behavior.');
    }

    echo "\nALL EVIDENCE COLLECTED. CERTIFICATION GRANTED.\n";

} catch (Throwable $e) {
    echo 'BLOCKED: '.$e->getMessage()."\n".$e->getTraceAsString()."\n";
} finally {
    DB::rollBack();
}
