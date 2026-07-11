#!/bin/bash
tests=(
"tests/Feature/BrandControllerTest.php"
"tests/Feature/CommerceIntelligenceTest.php"
"tests/Feature/CompatibilityControllerTest.php"
"tests/Feature/InventoryControllerTest.php"
"tests/Feature/PCBuilderTest.php"
"tests/Feature/PricingEngineTest.php"
"tests/Unit/DomainLayerTest.php"
)

echo "Baseline Initial Count: $(php artisan tinker --execute="echo DB::table('attribute_families')->count();")"

for test in "${tests[@]}"; do
    if [ -f "$test" ]; then
        echo "==================================="
        echo "Running $test"
        echo "Before: $(php artisan tinker --execute="echo DB::table('attribute_families')->count();")"
        php artisan test "$test" > /dev/null
        echo "After: $(php artisan tinker --execute="echo DB::table('attribute_families')->count();")"
    fi
done
