<?php

namespace App\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\UnauthorizedException;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminService extends BaseService
{
    /**
     * Admin login
     */
    public function login(array $credentials): array
    {
        $admin = Admin::where('email', $credentials['email'])->first();

        if (!$admin || !Hash::check($credentials['password'], $admin->password_hash)) {
            throw new UnauthorizedException('Invalid admin credentials');
        }

        if ($admin->isPending()) {
            throw new UnauthorizedException('Admin account is pending approval');
        }

        if ($admin->isBanned()) {
            throw new UnauthorizedException('Admin account has been banned');
        }

        if (!$admin->isActive()) {
            throw new UnauthorizedException('Admin account is not active');
        }

        // Log login action
        $admin->logAction('login');

        // Create token with role-specific abilities
        $abilities = $admin->isSuper() ? ['*'] : ['api:read'];
        $token = $admin->createToken('admin-token', $abilities)->plainTextToken;

        return [
            'admin' => $admin,
            'token' => $token
        ];
    }

    /**
     * Register new admin
     */
    public function register(array $data): Admin
    {
        // Check if email already exists
        if (Admin::where('email', $data['email'])->exists()) {
            throw new ConflictException('Admin with this email already exists');
        }

        // Create admin with pending status
        $admin = Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => Hash::make($data['password']),
            'role' => 'moderator', // All new registrations are moderators
            'status' => 'pending'  // Requires super admin approval
        ]);

        return $admin;
    }

    /**
     * Check if admin has permission for specific action
     */
    public function hasPermission(Admin $admin, string $permission): bool
    {
        if ($admin->isSuper()) {
            return true; // Super admin has all permissions
        }

        // Define moderator permissions
        $moderatorPermissions = [
            'api:read',
            'profile:view',
            'profile:update'
        ];

        return in_array($permission, $moderatorPermissions);
    }

    /**
     * Get admin statistics (super admin only)
     */
    public function getStatistics(): array
    {
        return [
            'total_admins' => Admin::count(),
            'active_admins' => Admin::active()->count(),
            'pending_admins' => Admin::pending()->count(),
            'banned_admins' => Admin::banned()->count(),
            'super_admins' => Admin::where('role', 'super')->active()->count(),
            'moderators' => Admin::where('role', 'moderator')->active()->count(),
        ];
    }

    /**
     * Get pending admin registrations (super admin only)
     */
    public function getPendingAdmins(): array
    {
        return Admin::pending()->with('approver')->orderBy('created_at', 'desc')->get()->toArray();
    }

    /**
     * Approve pending admin (super admin only)
     */
    public function approvePendingAdmin(Admin $pendingAdmin, Admin $approver): bool
    {
        if (!$approver->isSuper()) {
            throw new UnauthorizedException('Only super admins can approve pending admins');
        }

        if (!$pendingAdmin->isPending()) {
            throw new ConflictException('Admin is not in pending status');
        }

        $result = $pendingAdmin->approve($approver);
        
        if ($result) {
            $approver->logAction('admin_approved', Admin::class, $pendingAdmin->id);
        }

        return $result;
    }

    /**
     * Reject pending admin (super admin only)
     */
    public function rejectPendingAdmin(Admin $pendingAdmin, Admin $rejector, string $reason): bool
    {
        if (!$rejector->isSuper()) {
            throw new UnauthorizedException('Only super admins can reject pending admins');
        }

        if (!$pendingAdmin->isPending()) {
            throw new ConflictException('Admin is not in pending status');
        }

        $result = $pendingAdmin->reject($rejector, $reason);
        
        if ($result) {
            $rejector->logAction('admin_rejected', Admin::class, $pendingAdmin->id);
        }

        return $result;
    }

    /**
     * Ban admin (super admin only)
     */
    public function banAdmin(Admin $admin, Admin $banner, string $reason): bool
    {
        if (!$banner->isSuper()) {
            throw new UnauthorizedException('Only super admins can ban admins');
        }

        if ($admin->isSuper()) {
            throw new ConflictException('Cannot ban super admin');
        }

        if ($admin->id === $banner->id) {
            throw new ConflictException('Cannot ban yourself');
        }

        $result = $admin->ban($banner, $reason);
        
        if ($result) {
            $banner->logAction('admin_banned', Admin::class, $admin->id);
        }

        return $result;
    }

    /**
     * Unban admin (super admin only)
     */
    public function unbanAdmin(Admin $admin, Admin $unbanner): bool
    {
        if (!$unbanner->isSuper()) {
            throw new UnauthorizedException('Only super admins can unban admins');
        }

        if (!$admin->isBanned()) {
            throw new ConflictException('Admin is not banned');
        }

        $result = $admin->unban($unbanner);
        
        if ($result) {
            $unbanner->logAction('admin_unbanned', Admin::class, $admin->id);
        }

        return $result;
    }

    /**
     * Delete admin (super admin only)
     */
    public function deleteAdmin(Admin $admin, Admin $deleter): bool
    {
        if (!$deleter->isSuper()) {
            throw new UnauthorizedException('Only super admins can delete admins');
        }

        if ($admin->isSuper()) {
            throw new ConflictException('Cannot delete super admin');
        }

        if ($admin->id === $deleter->id) {
            throw new ConflictException('Cannot delete yourself');
        }

        $deleter->logAction('admin_deleted', Admin::class, $admin->id);
        
        return $admin->delete();
    }
}