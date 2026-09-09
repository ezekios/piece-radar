<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id',
    'matched_part_id',
    'vehicle_brand',
    'vehicle_model',
    'vehicle_year',
    'part_name',
    'part_category',
    'status',
    'matched_at',
])]
class SavedPartSearch extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function matchedPart(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'matched_part_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'vehicle_year' => 'integer',
            'matched_at' => 'datetime',
        ];
    }
}
