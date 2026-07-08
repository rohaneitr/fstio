<?php

declare(strict_types=1);

namespace App\Foundation\Exceptions;

use App\Foundation\Contracts\DomainExceptionInterface;
use Exception;

class BusinessException extends Exception implements DomainExceptionInterface
{
    /**
     * Create a new BusinessException instance.
     */
    public static function invalidInput(string $message = 'Invalid input parameters'): self
    {
        return new self($message, 422);
    }

    /**
     * Create exception for unauthorized access.
     */
    public static function unauthorized(string $message = 'Unauthorized action'): self
    {
        return new self($message, 403);
    }
}
