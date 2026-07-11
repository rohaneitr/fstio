<?php

use Illuminate\Contracts\Console\Kernel;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$events = app('events');
$listeners = $events->getListeners('checkout.cart.collect.totals.before');
$bound = [];
foreach ($listeners as $listener) {
    if (is_array($listener) && is_object($listener[0])) {
        $bound[] = get_class($listener[0]).'@'.$listener[1];
    } elseif (is_string($listener)) {
        $bound[] = $listener;
    } elseif ($listener instanceof Closure) {
        $bound[] = 'Closure';
    }
}
echo "Listeners for checkout.cart.collect.totals.before: \n".json_encode($bound, JSON_PRETTY_PRINT);
