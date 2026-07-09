<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Models\HardwareProfile;

class UpgradeAdvisor
{
    public function __construct(
        private RecommendationScoringEngine $scoringEngine
    ) {}

    /**
     * Identify the most impactful compatible upgrades.
     */
    public function advise(HardwareProfile $currentProfile, array $candidates): array
    {
        $currentScores = $this->scoringEngine->calculate($currentProfile, 0.0);
        $currentPerf = max($currentScores->getGamingScore(), $currentScores->getProductivityScore());

        $upgrades = [];

        foreach ($candidates as $cand) {
            $candProfile = HardwareProfile::fromProduct($cand);
            $candPrice = (float) ($cand->price ?? 0);
            if ($candPrice <= 0) {
                continue;
            }

            $candScores = $this->scoringEngine->calculate($candProfile, $candPrice);
            $candPerf = max($candScores->getGamingScore(), $candScores->getProductivityScore());

            if ($candPerf > $currentPerf) {
                $perfDelta = $candPerf - $currentPerf;
                // Performance improvement per dollar spent
                $valueDelta = $perfDelta / $candPrice;

                $upgrades[] = [
                    'product' => $cand,
                    'performance_delta' => round($perfDelta, 2),
                    'value_delta' => round($valueDelta * 100, 2),
                ];
            }
        }

        // Sort by value_delta desc (most improvement per dollar spent)
        usort($upgrades, fn ($a, $b) => $b['value_delta'] <=> $a['value_delta']);

        return $upgrades;
    }
}
