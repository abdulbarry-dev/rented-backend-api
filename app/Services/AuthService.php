<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService extends BaseService
{
    /**
     * Register a new user with token generation
     */
    public function register(array $data): array
    {
        $user = $this->handleUniqueConstraint(function () use ($data) {
            return User::create([
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'gender' => $data['gender'],
                'password_hash' => Hash::make($data['password']),
                'role' => $data['role'] ?? 'customer', // Default to customer
                'is_active' => true,
            ]);
        }, 'email or phone');

        // Create token for newly registered user
        $abilities = ['*']; // Users get full access to their resources
        $tokenResult = $user->createToken('user-token', $abilities, now()->addDays(30));
        $token = $tokenResult->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'token_expires_at' => $tokenResult->accessToken->expires_at
        ];
    }

    /**
     * User login with token generation
     */
    public function login(array $credentials): array
    {
        $loginField = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        
        $user = User::where($loginField, $credentials['login'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password_hash)) {
            throw new \App\Exceptions\UnauthorizedException('Invalid user credentials');
        }

        if (!$user->is_active) {
            throw new \App\Exceptions\UnauthorizedException('User account is not active');
        }

        // Create token with user abilities
        $abilities = ['*']; // Users get full access to their resources
        $tokenResult = $user->createToken('user-token', $abilities, now()->addDays(30));
        $token = $tokenResult->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'token_expires_at' => $tokenResult->accessToken->expires_at
        ];
    }

    /**
     * Attempt to authenticate user with email or phone (legacy method)
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