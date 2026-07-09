<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    protected $table = 'brands';

    protected $fillable = [
        'name',
        'slug',
        'logo_light',
        'logo_dark',
        'website_url',
        'status',
        'description',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'sort_order',
    ];

    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the series for the brand.
     */
    public function series(): HasMany
    {
        return $this->hasMany(BrandSeries::class, 'brand_id');
    }
}
