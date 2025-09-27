<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class UserVerification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'verification_status',
        'image_paths',
        'notes',
        'reviewed_by',
        'submitted_at',
        'reviewed_at'
    ];

    protected $casts = [
        'image_paths' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    public function getImageUrlsAttribute(): array
    {
        return array_map(fn($path) => Storage::url($path), $this->image_paths ?? []);
    }

    public function scopePending($query)
    {
        return $query->where('verification_status', 'pending');
    }

    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    public function scopeRejected($query)
    {
        return $query->where('verification_status', 'rejected');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function isPending(): bool
    {
        return $this->verification_status === 'pending';
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    public function isRejected(): bool
    {
        return $this->verification_status === 'rejected';
    }
}
