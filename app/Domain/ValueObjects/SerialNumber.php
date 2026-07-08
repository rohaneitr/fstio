<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use App\Domain\Exceptions\InvalidSerialException;

final readonly class SerialNumber
{
    public function __construct(private string $value)
    {
        if (! $this->isValid($value)) {
            throw new InvalidSerialException('Serial number must be alphanumeric and 4-64 characters.');
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    private function isValid(string $value): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9\-\/]{4,64}$/', $value);
    }
}
