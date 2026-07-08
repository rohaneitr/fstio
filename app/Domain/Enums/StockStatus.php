<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum StockStatus: string
{
    case IN_STOCK = 'in_stock';
    case OUT_OF_STOCK = 'out_of_stock';
    case PRE_ORDER = 'pre_order';
    case BACKORDER = 'backorder';
}
