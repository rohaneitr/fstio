<?php

declare(strict_types=1);

namespace App\Foundation\Money;

final readonly class Currency
{
    public function __construct(
        private string $code,
        private string $symbol
    ) {}

    public static function BDT(): self
    {
        return new self('BDT', '৳');
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function equals(Currency $other): bool
    {
        return $this->code === $other->getCode();
    }
}
