<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'scrapyard_id',
    'stock_origin',
    'license_plate',
    'brand',
    'model',
    'year',
    'version',
    'engine',
    'fuel',
    'color',
    'mileage',
    'status',
    'arrival_date',
])]
class Vehicle extends Model
{
    public function scrapyard(): BelongsTo
    {
        return $this->belongsTo(Scrapyard::class);
    }

    public function parts(): HasMany
    {
        return $this->hasMany(Part::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(VehicleImage::class)->orderBy('position');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'mileage' => 'integer',
            'arrival_date' => 'date',
        ];
    }
}
