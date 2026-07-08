<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use App\Domain\Enums\WarrantyType;
use App\Domain\Exceptions\InvalidWarrantyException;

final readonly class Warranty
{
    public function __construct(
        private int $months,
        private WarrantyType $type
    ) {
        if ($months < 0 || $months > 120) {
            throw new InvalidWarrantyException('Warranty period must be between 0 and 120 months.');
        }
    }

    public function getMonths(): int
    {
        return $this->months;
    }

    public function getType(): WarrantyType
    {
        return $this->type;
    }

    public function getFormatted(): string
    {
        if ($this->months === 0) {
            return 'No Warranty';
        }

        $years = intdiv($this->months, 12);
        $rem = $this->months % 12;

        $period = $years > 0 ? "{$years} Year".($years > 1 ? 's' : '') : '';
        if ($rem > 0) {
            $period .= ($period ? ' ' : '')."{$rem} Month".($rem > 1 ? 's' : '');
        }

        return $period." ({$this->type->value} Warranty)";
    }
}
