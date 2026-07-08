<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrandSeries extends Model
{
    protected $table = 'brand_series';

    protected $fillable = [
        'brand_id',
        'name',
        'slug',
    ];

    /**
     * Get the brand that owns the series.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }
}
