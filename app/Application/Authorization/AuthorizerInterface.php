<?php

declare(strict_types=1);

namespace App\Application\Authorization;

interface AuthorizerInterface
{
    /**
     * Check if current user is authorized to perform the action.
     */
    public function authorize(string $ability): void;
}
