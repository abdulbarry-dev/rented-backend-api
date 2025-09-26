<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
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
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'password_hash' => 'hashed',
        ];
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
     * Check if admin is a super admin.
     */
    public function isSuper(): bool
    {
        return $this->role === 'super' && $this->is_active;
    }

    /**
     * Check if admin is a moderator.
     */
    public function isModerator(): bool
    {
        return $this->role === 'moderator' && $this->is_active;
    }

    /**
     * Check if admin is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if admin can manage other admins.
     */
    public function canManageAdmins(): bool
    {
        return $this->isSuper();
    }

    /**
     * Check if admin can access all APIs.
     */
    public function hasFullAccess(): bool
    {
        return $this->isSuper();
    }

    /**
     * Get admin permissions based on role
     */
    public function getPermissions(): array
    {
        if ($this->isSuper()) {
            return ['*']; // Super admin has all permissions
        }
        
        if ($this->isModerator()) {
            return [
                'profile:view',
                'profile:update',
                'auth:logout'
            ];
        }
        
        return []; // Inactive or unknown role
    }

    /**
     * Scope to filter active admins.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by role.
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Relationship with admin actions
     */
    public function actions()
    {
        return $this->hasMany(AdminAction::class);
    }

    /**
     * Log admin action
     */
    public function logAction(string $action, ?string $targetType = null, ?int $targetId = null): void
    {
        $this->actions()->create([
            'action' => $action,
            'target_type' => $targetType ?? 'system',
            'target_id' => $targetId,
        ]);
    }
}
