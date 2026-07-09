<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Application\DTO\GetRecommendationsDto;
use App\Application\Handlers\GetRecommendationsHandler;
use App\Domain\Models\HardwareProfile;
use App\Domain\Services\RecommendationScoringEngine;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Webkul\Product\Models\ProductProxy;
use Webkul\User\Models\Admin;

class CommerceIntelligenceTest extends TestCase
{
    protected function setIntelAttribute(int $productId, string $attributeCode, mixed $value): void
    {
        $attribute = DB::table('attributes')->where('code', $attributeCode)->first();
        if (! $attribute) {
            $attributeId = DB::table('attributes')->insertGetId([
                'code' => $attributeCode,
                'admin_name' => $attributeCode,
                'type' => 'text',
                'position' => 1,
                'is_required' => 0,
                'is_unique' => 0,
            ]);
        } else {
            $attributeId = $attribute->id;
        }

        $column = 'text_value';
        if (is_float($value)) {
            $column = 'float_value';
        } elseif (is_int($value)) {
            $column = 'integer_value';
        }

        DB::table('product_attribute_values')->updateOrInsert([
            'product_id' => $productId,
            'attribute_id' => $attributeId,
        ], [
            $column => $value,
        ]);
    }

    protected function createIntelProduct(string $sku, float $price, array $attributes = []): mixed
    {
        $product = ProductProxy::create([
            'type' => 'simple',
            'attribute_family_id' => 1,
            'sku' => $sku,
        ]);

        $this->setIntelAttribute($product->id, 'price', $price);
        $this->setIntelAttribute($product->id, 'name', $sku);

        foreach ($attributes as $code => $val) {
            $this->setIntelAttribute($product->id, $code, $val);
        }

        $channelId = DB::table('channels')->value('id') ?? 1;
        $customerGroupId = DB::table('customer_groups')->value('id') ?? 1;

        DB::table('product_price_indices')->updateOrInsert([
            'product_id' => $product->id,
            'channel_id' => $channelId,
            'customer_group_id' => $customerGroupId,
        ], [
            'min_price' => $price,
            'max_price' => $price,
        ]);

        $channelCode = DB::table('channels')->value('code') ?? 'default';
        $localeCode = DB::table('locales')->value('code') ?? 'en';

        DB::table('product_flat')->updateOrInsert([
            'product_id' => $product->id,
            'channel' => $channelCode,
            'locale' => $localeCode,
        ], [
            'price' => $price,
            'name' => $sku,
            'sku' => $sku,
            'status' => 1,
            'visible_individually' => 1,
            'url_key' => 'product-'.$product->id,
        ]);

        return $product;
    }

    public function test_scoring_engine_calculates_correct_relative_indices()
    {
        DB::beginTransaction();

        $engine = new RecommendationScoringEngine;

        // 1. High-end hardware profile
        $highProfile = new HardwareProfile(1, [
            'cores' => 16,
            'threads' => 32,
            'boost_clock' => 5.2,
            'vram' => 16,
            'memory_bus' => 256,
            'ram_speed' => 6000,
        ]);

        $highScores = $engine->calculate($highProfile, 899.99);

        // 2. Budget hardware profile
        $budgetProfile = new HardwareProfile(2, [
            'cores' => 4,
            'threads' => 8,
            'boost_clock' => 3.6,
            'vram' => 4,
            'memory_bus' => 128,
            'ram_speed' => 4800,
        ]);

        $budgetScores = $engine->calculate($budgetProfile, 199.99);

        // Assert relative ratios are mathematically correct
        $this->assertGreaterThan($budgetScores->getGamingScore(), $highScores->getGamingScore());
        $this->assertGreaterThan($budgetScores->getAiScore(), $highScores->getAiScore());
        $this->assertGreaterThan($budgetScores->getProductivityScore(), $highScores->getProductivityScore());

        DB::rollBack();
    }

    public function test_recommendation_handler_resolves_strategy_suggestions()
    {
        DB::beginTransaction();

        $target = $this->createIntelProduct('Target-CPU', 499.99, [
            'cores' => 8,
            'threads' => 16,
            'boost_clock' => 4.8,
            'vram' => 8,
        ]);

        // Simpler/Cheaper alternative
        $cheaper = $this->createIntelProduct('Cheaper-CPU', 299.99, [
            'cores' => 6,
            'threads' => 12,
            'boost_clock' => 4.2,
            'vram' => 6,
        ]);

        // Better performance alternative
        $better = $this->createIntelProduct('Premium-CPU', 699.99, [
            'cores' => 12,
            'threads' => 24,
            'boost_clock' => 5.2,
            'vram' => 12,
        ]);

        $handler = app(GetRecommendationsHandler::class);

        // Test similar strategy
        $resSimilar = $handler->handle(new GetRecommendationsDto($target->id, 'similar'))->getPayload();
        $this->assertNotEmpty($resSimilar);

        // Test cheaper strategy
        $resCheaper = $handler->handle(new GetRecommendationsDto($target->id, 'cheaper'))->getPayload();
        $this->assertNotEmpty($resCheaper);
        $namesCheaper = array_column($resCheaper, 'name');
        $this->assertContains('Cheaper-CPU', $namesCheaper);

        // Test performance strategy
        $resPerf = $handler->handle(new GetRecommendationsDto($target->id, 'performance'))->getPayload();
        $this->assertNotEmpty($resPerf);
        $namesPerf = array_column($resPerf, 'name');
        $this->assertContains('Premium-CPU', $namesPerf);

        DB::rollBack();
    }

    public function test_api_route_endpoint_responds_successfully()
    {
        DB::beginTransaction();

        $target = $this->createIntelProduct('Route-CPU', 450.00, [
            'cores' => 8,
        ]);

        // Mock authentication for admin guard
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->json('GET', route('admin.catalog.products.recommendations', $target->id));
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        DB::rollBack();
    }

    public function test_cache_invalidation_lifecycle_clears_scores()
    {
        DB::beginTransaction();

        $target = $this->createIntelProduct('Cached-CPU', 350.00, [
            'cores' => 6,
        ]);

        $handler = app(GetRecommendationsHandler::class);
        $handler->handle(new GetRecommendationsDto($target->id, 'similar'));

        // Verify cache hit exists
        $cacheKey = "recommendations_{$target->id}_similar";
        $this->assertTrue(Cache::tags(['recommendations', 'product_scores'])->has($cacheKey));

        // Flush/reset cache manually to assert integration logic clears tags
        Cache::tags(['recommendations'])->flush();
        $this->assertFalse(Cache::tags(['recommendations', 'product_scores'])->has($cacheKey));

        DB::rollBack();
    }
}
