<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getAllUsers(int $perPage = 15): LengthAwarePaginator
    {
        return User::select(['id', 'first_name', 'last_name', 'email', 'phone', 'created_at', 'updated_at'])
                  ->paginate($perPage);
    }

    public function createUser(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        return User::create($data);
    }

    public function getUserById(int $id): ?User
    {
        return User::select(['id', 'first_name', 'last_name', 'email', 'phone', 'created_at', 'updated_at'])
                  ->find($id);
    }

    public function updateUser(User $user, array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        
        $user->update($data);
        return $user->fresh();
    }

    public function deleteUser(User $user): bool
    {
        return $user->delete();
    }
}
