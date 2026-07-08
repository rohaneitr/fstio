<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class PartNumber
{
    public function __construct(private string $value)
    {
        if (! $this->isValid($value)) {
            throw new InvalidArgumentException('Part number must be alphanumeric, between 3 and 64 characters.');
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    private function isValid(string $value): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9\-\_\/\.]{3,64}$/', $value);
    }
}
