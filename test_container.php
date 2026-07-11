<?php

use App\Domain\Services\PriceCalculator;
use Illuminate\Contracts\Console\Kernel;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();
$calc = app(PriceCalculator::class);
echo 'Container Resolved: '.get_class($calc).PHP_EOL;
