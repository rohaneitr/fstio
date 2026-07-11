<?php

use Illuminate\Contracts\Console\Kernel;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$events = app('events');
$listeners = $events->getRawListeners();
$checkoutEvents = [];
foreach (array_keys($listeners) as $eventName) {
    if (str_contains($eventName, 'checkout.cart')) {
        $checkoutEvents[] = $eventName;
    }
}
echo "Checkout events: \n".json_encode($checkoutEvents, JSON_PRETTY_PRINT);
