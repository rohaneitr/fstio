<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum WarrantyType: string
{
    case OFFICIAL = 'Official';
    case UNOFFICIAL = 'Unofficial';
    case DEALER = 'Dealer';
    case INTERNATIONAL = 'International';
}
