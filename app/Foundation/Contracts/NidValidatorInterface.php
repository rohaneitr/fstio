<?php

declare(strict_types=1);

namespace App\Foundation\Contracts;

interface NidValidatorInterface
{
    /**
     * Validate Bangladesh National ID number.
     */
    public function validateNid(string $nid): bool;
}
