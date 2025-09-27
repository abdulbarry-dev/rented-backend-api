<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password_hash',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password_hash' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Check if the user's email is verified.
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Get the user's display name for notifications.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->full_name ?: $this->email;
    }

    /**
     * Scope to filter verified users.
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Scope to filter unverified users.
     */
    public function scopeUnverified($query)
    {
        return $query->whereNull('email_verified_at');
    }

    /**
     * Check if user is a customer.
     */
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    /**
     * Check if user is a seller.
     */
    public function isSeller(): bool
    {
        return $this->role === 'seller';
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Scope to filter active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter inactive users.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope to filter by role.
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Get password attribute (compatibility method).
     */
    public function getPasswordAttribute()
    {
        return $this->password_hash;
    }

    /**
     * Set password attribute (compatibility method).
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password_hash'] = $value;
    }

    /**
     * Get the user's verification record.
     */
    public function verification()
    {
        return $this->hasOne(UserVerification::class);
    }

    /**
     * Check if user has a verified identity.
     */
    public function isVerified(): bool
    {
        return $this->verification && $this->verification->isVerified();
    }

    /**
     * Check if user has a verification in progress.
     */
    public function hasVerificationInProgress(): bool
    {
        return $this->verification && $this->verification->isPending();
    }

    /**
     * Check if user's verification was rejected.
     */
    public function isVerificationRejected(): bool
    {
        return $this->verification && $this->verification->isRejected();
    }

    /**
     * Get user's verification status.
     */
    public function getVerificationStatus(): ?string
    {
        return $this->verification ? $this->verification->verification_status : null;
    }

    /**
     * Check if user can create/edit products.
     */
    public function canManageProducts(): bool
    {
        return $this->isVerified() && $this->isSeller();
    }

    /**
     * Check if user can rent/buy products.
     */
    public function canRentProducts(): bool
    {
        return $this->isVerified();
    }

    /**
     * Check if user is restricted to browsing only.
     */
    public function isBrowsingOnly(): bool
    {
        return !$this->isVerified();
    }

    /**
     * Scope to filter verified users (identity verified).
     */
    public function scopeIdentityVerified($query)
    {
        return $query->whereHas('verification', function ($q) {
            $q->where('verification_status', 'verified');
        });
    }

    /**
     * Scope to filter unverified users (identity not verified).
     */
    public function scopeIdentityUnverified($query)
    {
        return $query->whereDoesntHave('verification')
            ->orWhereHas('verification', function ($q) {
                $q->where('verification_status', '!=', 'verified');
            });
    }
}
