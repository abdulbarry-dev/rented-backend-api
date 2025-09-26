<?php

namespace App\Services;

use App\Models\User;
use App\Exceptions\ResourceNotFoundException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService extends BaseService
{
    /**
     * Get all users with pagination
     */
    public function getAllUsers(int $perPage = 15): LengthAwarePaginator
    {
        return User::select(['id', 'first_name', 'last_name', 'email', 'phone', 'role', 'is_active', 'created_at', 'updated_at'])
                  ->active() // Only show active users
                  ->paginate($perPage);
    }

    /**
     * Create a new user
     */
    public function createUser(array $data): User
    {
        return $this->handleUniqueConstraint(function () use ($data) {
            $data['password_hash'] = Hash::make($data['password']);
            unset($data['password']); // Remove the original password field
            return User::create($data);
        }, 'email or phone');
    }

    /**
     * Get user by ID or throw exception
     */
    public function getUserById(int $id): User
    {
        return $this->findOrFail(User::class, $id);
    }

    /**
     * Get user by ID for display (with selected fields)
     */
    public function getUserForDisplay(int $id): ?User
    {
        return User::select(['id', 'first_name', 'last_name', 'email', 'phone', 'role', 'is_active', 'created_at', 'updated_at'])
                  ->find($id);
    }

    /**
     * Update user data
     */
    public function updateUser(User $user, array $data): User
    {
        return $this->executeQuery(function () use ($user, $data) {
            if (isset($data['password'])) {
                $data['password_hash'] = Hash::make($data['password']);
                unset($data['password']); // Remove the original password field
            }
            
            $user->update($data);
            return $user->fresh();
        }, 'Failed to update user');
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user): bool
    {
        return $this->executeQuery(function () use ($user) {
            return $user->delete();
        }, 'Failed to delete user');
    }

    /**
     * Get users by role
     */
    public function getUsersByRole(string $role, int $perPage = 15): LengthAwarePaginator
    {
        return User::select(['id', 'first_name', 'last_name', 'email', 'phone', 'role', 'is_active', 'created_at', 'updated_at'])
                  ->byRole($role)
                  ->active()
                  ->paginate($perPage);
    }

    /**
     * Activate user
     */
    public function activateUser(User $user): User
    {
        return $this->executeQuery(function () use ($user) {
            $user->update(['is_active' => true]);
            return $user->fresh();
        }, 'Failed to activate user');
    }

    /**
     * Deactivate user
     */
    public function deactivateUser(User $user): User
    {
        return $this->executeQuery(function () use ($user) {
            $user->update(['is_active' => false]);
            return $user->fresh();
        }, 'Failed to deactivate user');
    }

    /**
     * Change user role
     */
    public function changeUserRole(User $user, string $role): User
    {
        return $this->executeQuery(function () use ($user, $role) {
            $user->update(['role' => $role]);
            return $user->fresh();
        }, 'Failed to change user role');
    }
}
