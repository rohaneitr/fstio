<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\ComponentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Product\Models\ProductProxy;

class BuildItem extends Model
{
    public $timestamps = false;

    protected $table = 'pc_build_items';

    protected $fillable = [
        'build_id',
        'product_id',
        'component_type',
    ];

    protected $casts = [
        'component_type' => ComponentType::class,
    ];

    /**
     * Get the build aggregate that owns the build item.
     */
    public function build(): BelongsTo
    {
        return $this->belongsTo(Build::class, 'build_id');
    }

    /**
     * Get the product details of the component.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductProxy::modelClass(), 'product_id');
    }
}
