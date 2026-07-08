<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum ProductLifecycle: string
{
    case ACTIVE = 'active';
    case DISCONTINUED = 'discontinued';
    case PRE_ORDER = 'pre_order';
    case COMING_SOON = 'coming_soon';
}
