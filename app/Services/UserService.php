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
        return User::select(['id', 'first_name', 'last_name', 'email', 'phone', 'created_at', 'updated_at'])
                  ->paginate($perPage);
    }

    /**
     * Create a new user
     */
    public function createUser(array $data): User
    {
        return $this->handleUniqueConstraint(function () use ($data) {
            $data['password'] = Hash::make($data['password']);
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
        return User::select(['id', 'first_name', 'last_name', 'email', 'phone', 'created_at', 'updated_at'])
                  ->find($id);
    }

    /**
     * Update user data
     */
    public function updateUser(User $user, array $data): User
    {
        return $this->executeQuery(function () use ($user, $data) {
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
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
}
