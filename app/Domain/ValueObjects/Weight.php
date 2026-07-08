<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class Weight
{
    public function __construct(
        private float $value,
        private string $unit = 'kg'
    ) {
        if ($value <= 0) {
            throw new InvalidArgumentException('Weight value must be greater than zero.');
        }

        if (! in_array(strtolower($unit), ['kg', 'g'])) {
            throw new InvalidArgumentException('Weight unit must be either kg or g.');
        }
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function toGrams(): float
    {
        return strtolower($this->unit) === 'kg' ? $this->value * 1000 : $this->value;
    }
}
