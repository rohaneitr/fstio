<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    protected $table = 'districts';

    protected $fillable = [
        'division_id',
        'name',
    ];

    /**
     * Get upazilas under this district.
     */
    public function upazilas(): HasMany
    {
        return $this->hasMany(Upazila::class, 'district_id');
    }
}
