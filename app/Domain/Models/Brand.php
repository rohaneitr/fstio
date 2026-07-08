<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    protected $table = 'brands';

    protected $fillable = [
        'slug',
        'logo_light',
        'logo_dark',
        'website_url',
    ];

    /**
     * Get the series for the brand.
     */
    public function series(): HasMany
    {
        return $this->hasMany(BrandSeries::class, 'brand_id');
    }
}
