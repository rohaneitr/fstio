<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSerial extends Model
{
    protected $table = 'product_serials';

    protected $fillable = [
        'product_id',
        'inventory_source_id',
        'serial_number',
        'status',
    ];
}
