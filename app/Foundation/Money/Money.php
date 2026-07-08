<?php

declare(strict_types=1);

namespace App\Foundation\Money;

use InvalidArgumentException;

final readonly class Money
{
    private function __construct(
        private int $amount, // Amount in lowest subunit (e.g. paisa/cents)
        private Currency $currency
    ) {}

    public static function fromSubunits(int $amount, Currency $currency): self
    {
        return new self($amount, $currency);
    }

    public static function fromDecimal(float $amount, Currency $currency): self
    {
        return new self((int) round($amount * 100), $currency);
    }

    public static function BDT(float $amount): self
    {
        return self::fromDecimal($amount, Currency::BDT());
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getDecimalAmount(): float
    {
        return $this->amount / 100;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function add(Money $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amount + $other->getAmount(), $this->currency);
    }

    public function subtract(Money $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amount - $other->getAmount(), $this->currency);
    }

    public function multiply(float $multiplier): self
    {
        return new self((int) round($this->amount * $multiplier), $this->currency);
    }

    private function assertSameCurrency(Money $other): void
    {
        if (! $this->currency->equals($other->getCurrency())) {
            throw new InvalidArgumentException('Currencies must match for money arithmetic.');
        }
    }
}
