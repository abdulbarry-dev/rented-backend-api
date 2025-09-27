<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'owner_id',
        'status'
    ];

    protected $casts = [
        'owner_id' => 'integer'
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function description(): HasOne
    {
        return $this->hasOne(ProductDescription::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function verification(): HasOne
    {
        return $this->hasOne(ProductVerification::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeByOwner($query, $ownerId)
    {
        return $query->where('owner_id', $ownerId);
    }

    public function scopeVerified($query)
    {
        return $query->whereHas('verification', function($q) {
            $q->where('verification_status', 'verified');
        });
    }

    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    public function getAverageRatingAttribute(): float
    {
        return $this->reviews()->avg('review_rate') ?? 0;
    }

    public function getTotalReviewsAttribute(): int
    {
        return $this->reviews()->count();
    }
}
