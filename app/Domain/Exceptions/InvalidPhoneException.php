<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use App\Foundation\Contracts\DomainExceptionInterface;
use Exception;

class InvalidPhoneException extends Exception implements DomainExceptionInterface {}
