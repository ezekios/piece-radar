<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'user_id',
    'name',
    'slug',
    'phone',
    'email',
    'address',
    'postal_code',
    'city',
    'description',
    'is_active',
])]
class Scrapyard extends Model
{
    use Notifiable;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function parts(): HasManyThrough
    {
        return $this->hasManyThrough(Part::class, Vehicle::class);
    }

    public function routeNotificationForMail(): ?string
    {
        return $this->email ?: $this->user?->email;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
