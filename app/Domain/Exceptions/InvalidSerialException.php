<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use App\Foundation\Contracts\DomainExceptionInterface;
use Exception;

class InvalidSerialException extends Exception implements DomainExceptionInterface {}
