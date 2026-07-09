<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

final readonly class ScoreMatrix
{
    public function __construct(
        private float $gamingScore,
        private float $aiScore,
        private float $productivityScore,
        private float $valueScore
    ) {}

    public function getGamingScore(): float
    {
        return $this->gamingScore;
    }

    public function getAiScore(): float
    {
        return $this->aiScore;
    }

    public function getProductivityScore(): float
    {
        return $this->productivityScore;
    }

    public function getValueScore(): float
    {
        return $this->valueScore;
    }

    public function toArray(): array
    {
        return [
            'gaming' => $this->gamingScore,
            'ai' => $this->aiScore,
            'productivity' => $this->productivityScore,
            'value' => $this->valueScore,
        ];
    }
}
