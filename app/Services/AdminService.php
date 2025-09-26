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

        if (!$admin->is_active) {
            throw new UnauthorizedException('Admin account is deactivated');
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

        // Create admin
        $admin = Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'moderator',
            'is_active' => true
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
            'super_admins' => Admin::where('role', 'super')->count(),
            'moderators' => Admin::where('role', 'moderator')->count(),
        ];
    }
}