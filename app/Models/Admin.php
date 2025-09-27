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
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
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
            'password_hash' => 'hashed',
            'approved_at' => 'datetime',
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
        return $this->role === 'super' && $this->isActive();
    }

    /**
     * Check if admin is a moderator.
     */
    public function isModerator(): bool
    {
        return $this->role === 'moderator' && $this->isActive();
    }

    /**
     * Check if admin is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if admin is pending approval.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if admin is banned.
     */
    public function isBanned(): bool
    {
        return $this->status === 'banned';
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
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter pending admins.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to filter banned admins.
     */
    public function scopeBanned($query)
    {
        return $query->where('status', 'banned');
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
     * Relationship with approver admin
     */
    public function approver()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    /**
     * Admins approved by this admin
     */
    public function approvedAdmins()
    {
        return $this->hasMany(Admin::class, 'approved_by');
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

    /**
     * Approve admin (super admin only)
     */
    public function approve(Admin $approver): bool
    {
        if (!$approver->isSuper()) {
            return false;
        }

        return $this->update([
            'status' => 'active',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'rejection_reason' => null
        ]);
    }

    /**
     * Reject admin (super admin only)
     */
    public function reject(Admin $rejector, string $reason): bool
    {
        if (!$rejector->isSuper()) {
            return false;
        }

        return $this->update([
            'status' => 'banned',
            'approved_by' => $rejector->id,
            'approved_at' => now(),
            'rejection_reason' => $reason
        ]);
    }

    /**
     * Ban admin (super admin only)
     */
    public function ban(Admin $banner, string $reason): bool
    {
        if (!$banner->isSuper()) {
            return false;
        }

        return $this->update([
            'status' => 'banned',
            'rejection_reason' => $reason
        ]);
    }

    /**
     * Unban admin (super admin only)
     */
    public function unban(Admin $unbanner): bool
    {
        if (!$unbanner->isSuper()) {
            return false;
        }

        return $this->update([
            'status' => 'active',
            'rejection_reason' => null
        ]);
    }
}
