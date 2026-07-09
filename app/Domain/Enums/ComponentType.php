<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum ComponentType: string
{
    case CPU = 'cpu';
    case MOTHERBOARD = 'motherboard';
    case RAM = 'ram';
    case GPU = 'gpu';
    case CASE = 'case';
    case PSU = 'psu';
    case COOLER = 'cooler';
    case STORAGE = 'storage';
}
