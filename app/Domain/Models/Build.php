<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Build extends Model
{
    protected $table = 'pc_builds';

    protected $fillable = [
        'uuid',
        'name',
        'user_id',
        'total_price',
        'estimated_wattage',
        'version',
        'build_hash',
    ];

    /**
     * Get the child components of the build.
     */
    public function items(): HasMany
    {
        return $this->hasMany(BuildItem::class, 'build_id');
    }

    /**
     * Calculate build completion percentage (out of 6 required core components).
     */
    public function getCompletionPercentage(): int
    {
        $requiredTypes = ['cpu', 'motherboard', 'ram', 'gpu', 'case', 'psu'];
        $installedTypes = $this->items->pluck('component_type')->map(fn ($type) => $type->value)->toArray();

        $completed = 0;
        foreach ($requiredTypes as $req) {
            if (in_array($req, $installedTypes)) {
                $completed++;
            }
        }

        return (int) (($completed / count($requiredTypes)) * 100);
    }
}
