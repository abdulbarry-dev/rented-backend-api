<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService extends BaseService
{
    /**
     * Register a new user
     */
    public function register(array $data): User
    {
        return $this->handleUniqueConstraint(function () use ($data) {
            return User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password_hash' => Hash::make($data['password']),
                'role' => $data['role'] ?? 'customer', // Default to customer
                'is_active' => true,
            ]);
        }, 'email or phone');
    }

    /**
     * Attempt to authenticate user with email or phone
     */
    public function attempt(array $credentials): bool
    {
        $loginField = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        
        return Auth::attempt([
            $loginField => $credentials['login'],
            'password' => $credentials['password']
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user(): ?User
    {
        return Auth::user();
    }

    /**
     * Logout current user
     */
    public function logout(): void
    {
        Auth::logout();
    }
}