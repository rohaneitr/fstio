<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class Dimension
{
    public function __construct(
        private float $width,
        private float $height,
        private float $depth
    ) {
        if ($width <= 0 || $height <= 0 || $depth <= 0) {
            throw new InvalidArgumentException('Dimensions must be greater than zero.');
        }
    }

    public function getWidth(): float
    {
        return $this->width;
    }

    public function getHeight(): float
    {
        return $this->height;
    }

    public function getDepth(): float
    {
        return $this->depth;
    }

    public function getVolume(): float
    {
        return $this->width * $this->height * $this->depth;
    }
}
