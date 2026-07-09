<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Models\HardwareProfile;
use App\Domain\ValueObjects\ScoreMatrix;

class RecommendationScoringEngine
{
    /**
     * Calculate scores based on hardware profile EAV attributes and product price.
     */
    public function calculate(HardwareProfile $profile, float $price = 99.99): ScoreMatrix
    {
        $cores = (float) $profile->get('cores', 4);
        $threads = (float) $profile->get('threads', 8);
        $boostClock = (float) $profile->get('boost_clock', 3.5);
        $vram = (float) $profile->get('vram', 8);
        $memoryBus = (float) $profile->get('memory_bus', 128);
        $ramSpeed = (float) $profile->get('ram_speed', 5600);
        $ramCapacity = (float) $profile->get('ram_capacity', 16);
        $readSpeed = (float) $profile->get('sequential_read', 3500);
        $writeSpeed = (float) $profile->get('sequential_write', 3000);

        // Normalize metrics into standard scales [0, 1]
        $nCores = min(1.0, max(0.0, ($cores - 2) / 30.0));
        $nThreads = min(1.0, max(0.0, ($threads - 4) / 60.0));
        $nBoost = min(1.0, max(0.0, ($boostClock - 1.0) / 5.0));
        $nVram = min(1.0, max(0.0, ($vram - 2) / 22.0));
        $nBus = min(1.0, max(0.0, ($memoryBus - 64) / 320.0));
        $nRamSpeed = min(1.0, max(0.0, ($ramSpeed - 2133) / 6000.0));
        $nRamCap = min(1.0, max(0.0, ($ramCapacity - 4) / 124.0));
        $nRead = min(1.0, max(0.0, ($readSpeed - 500) / 7000.0));
        $nWrite = min(1.0, max(0.0, ($writeSpeed - 500) / 7000.0));

        // Gaming score calculations: dominated by GPU specs, single core boosts
        $gpuScore = (0.7 * $nVram) + (0.3 * $nBus);
        $cpuScore = (0.8 * $nBoost) + (0.2 * $nCores);
        $gaming = (0.6 * $gpuScore) + (0.25 * $cpuScore) + (0.15 * $nRamSpeed);

        // AI score calculations: dominated by VRAM and system memory
        $ai = (0.7 * $nVram) + (0.2 * $nRamCap) + (0.1 * $nRead);

        // Productivity & Rendering: cores count and storage speeds
        $render = (0.5 * (($nCores + $nThreads) / 2.0)) + (0.35 * $nRamCap) + (0.15 * $nWrite);

        $gamingScoreVal = round($gaming * 100, 2);
        $aiScoreVal = round($ai * 100, 2);
        $productivityVal = round($render * 100, 2);

        // Value score calculates performance per cost index
        $maxPerformance = max($gamingScoreVal, $aiScoreVal, $productivityVal);
        $valueScoreVal = $price > 0 ? round(($maxPerformance / $price) * 100, 2) : 100.0;

        return new ScoreMatrix($gamingScoreVal, $aiScoreVal, $productivityVal, $valueScoreVal);
    }
}
