<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Upazila extends Model
{
    protected $table = 'upazilas';

    protected $fillable = [
        'district_id',
        'name',
        'postal_code',
    ];

    /**
     * Get the district that owns the upazila.
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id');
    }
}
