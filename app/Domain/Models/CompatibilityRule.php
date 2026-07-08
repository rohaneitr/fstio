<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class CompatibilityRule extends Model
{
    protected $table = 'compatibility_rules';

    protected $fillable = [
        'parent_product_id',
        'child_product_id',
        'rule_type',
    ];
}
