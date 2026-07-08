<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum SerialStatus: string
{
    case AVAILABLE = 'available';
    case RESERVED = 'reserved';
    case SOLD = 'sold';
    case RETURNED = 'returned';
    case DEFECTIVE = 'defective';
}
