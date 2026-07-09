<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Models\HardwareProfile;

class HybridRecommendationPipeline
{
    public function __construct(
        private RecommendationScoringEngine $scoringEngine
    ) {}

    /**
     * Get sorted list of product recommendations.
     *
     * @param  array  $candidates  Collection of product models
     */
    public function suggestAlternatives($targetProduct, array $candidates, string $strategy = 'similar'): array
    {
        $targetProfile = HardwareProfile::fromProduct($targetProduct);
        $targetPrice = (float) ($targetProduct->price ?? 0);
        $targetScores = $this->scoringEngine->calculate($targetProfile, $targetPrice);

        $results = [];

        foreach ($candidates as $candidate) {
            if ((int) $candidate->id === (int) $targetProduct->id) {
                continue;
            }

            $candProfile = HardwareProfile::fromProduct($candidate);
            $candPrice = (float) ($candidate->price ?? 0);
            $candScores = $this->scoringEngine->calculate($candProfile, $candPrice);

            // Compute vector distance (CPU/GPU specs distance)
            $distance = sqrt(
                pow((float) $targetProfile->get('cores', 0) - (float) $candProfile->get('cores', 0), 2) +
                pow((float) $targetProfile->get('boost_clock', 0) - (float) $candProfile->get('boost_clock', 0), 2) +
                pow((float) $targetProfile->get('vram', 0) - (float) $candProfile->get('vram', 0), 2)
            );

            $results[] = [
                'product' => $candidate,
                'scores' => $candScores->toArray(),
                'distance' => $distance,
                'price' => $candPrice,
            ];
        }

        // Apply sorting based on target recommendation strategy
        if ($strategy === 'cheaper') {
            // Filter products that are cheaper and sort by value score
            $filtered = array_filter($results, fn ($r) => $r['price'] < $targetPrice);
            usort($filtered, fn ($a, $b) => $b['scores']['value'] <=> $a['scores']['value']);

            return array_values($filtered);
        }

        if ($strategy === 'performance') {
            // Sort by absolute gaming and productivity score
            usort($results, function ($a, $b) {
                $scoreA = max($a['scores']['gaming'], $a['scores']['productivity']);
                $scoreB = max($b['scores']['gaming'], $b['scores']['productivity']);

                return $scoreB <=> $scoreA;
            });

            return array_values($results);
        }

        // Default 'similar': Sort by closest Euclidean distance
        usort($results, fn ($a, $b) => $a['distance'] <=> $b['distance']);

        return array_values($results);
    }
}
