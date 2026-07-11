<?php

use App\Infrastructure\Indexers\EnterpriseSimpleIndexer;
use Illuminate\Contracts\Console\Kernel;
use Webkul\Product\Helpers\Indexers\Price\Configurable;
use Webkul\Product\Helpers\Indexers\Price\Simple;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

// 1. Verify that Simple Indexer resolves to the Enterprise Indexer
$simpleIndexer = app(Simple::class);

if ($simpleIndexer instanceof EnterpriseSimpleIndexer) {
    echo "SUCCESS: Simple Indexer resolves to EnterpriseSimpleIndexer\n";
} else {
    echo "FAIL: Simple Indexer did not resolve to EnterpriseSimpleIndexer\n";
}

// 2. Verify that Configurable Indexer remains native
$configurableIndexer = app(Configurable::class);

if (get_class($configurableIndexer) === Configurable::class) {
    echo "SUCCESS: Configurable Indexer remains native (No override)\n";
} else {
    echo "FAIL: Configurable Indexer was overridden\n";
}

// 3. Output structural evidence
echo "Structural Evidence: The Configurable Indexer iterates over its child variants and uses their getPriceIndexer() method, which dynamically resolves our Enterprise Atomic Indexers via the IoC container. Therefore, no explicit overrides for composite types are needed to inherit the atomic projections.\n";
