<?php

declare(strict_types=1);

namespace App\Foundation\Contracts;

interface BinValidatorInterface
{
    /**
     * Validate Bangladesh Business Identification Number (VAT registration number).
     */
    public function validateBin(string $bin): bool;
}
