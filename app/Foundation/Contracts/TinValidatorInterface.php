<?php

declare(strict_types=1);

namespace App\Foundation\Contracts;

interface TinValidatorInterface
{
    /**
     * Validate Bangladesh Tax Identification Number.
     */
    public function validateTin(string $tin): bool;
}
