<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use App\Domain\Exceptions\InvalidBrandException;

final readonly class Slug
{
    public function __construct(private string $value)
    {
        if (! $this->isValid($value)) {
            throw new InvalidBrandException('Slug must be a valid URL-friendly slug (lowercase, alphanumeric, dashes).');
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    private function isValid(string $value): bool
    {
        return (bool) preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value);
    }
}
