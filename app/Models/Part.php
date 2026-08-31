<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'vehicle_id',
    'name',
    'category',
    'reference',
    'oem_reference',
    'description',
    'condition',
    'status',
    'price',
    'is_published',
])]
class Part extends Model
{
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function partHoldRequests(): HasMany
    {
        return $this->hasMany(PartHoldRequest::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PartImage::class)->orderBy('position');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_published' => 'boolean',
        ];
    }
}
