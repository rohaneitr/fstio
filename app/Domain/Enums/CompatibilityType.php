<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum CompatibilityType: string
{
    case SOCKET = 'socket';
    case RAM = 'ram';
    case INTERFACE = 'interface';
    case FORM_FACTOR = 'form_factor';
    case POWER = 'power';
}
