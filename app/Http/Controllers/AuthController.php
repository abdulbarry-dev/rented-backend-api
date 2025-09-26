<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->register($request->validated());
            
            // Alternative 1: Standard Laravel Sanctum syntax
            $token = $user->createToken('API Token')->plainTextToken;
            
            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'user' => new UserResource($user),
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->only(['email', 'password']);
            
            if (!$this->authService->attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            $user = Auth::user();
            
            // Alternative 2: With token abilities and expiration
            $tokenResult = $user->createToken('API Token', ['*'], now()->addDays(30));
            $token = $tokenResult->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => new UserResource($user),
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'expires_at' => $tokenResult->accessToken->expires_at
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authenticated user profile
     */
    public function profile(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new UserResource($request->user())
        ]);
    }

    /**
     * Logout user (revoke current token)
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Laravel 11+ syntax for token revocation
            $request->user()->currentAccessToken()?->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout from all devices (revoke all tokens)
     */
    public function logoutAll(Request $request): JsonResponse
    {
        try {
            $request->user()->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out from all devices successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout from all devices failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh token (revoke current and create new)
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Delete current token
            $user->currentAccessToken()?->delete();
            
            // Alternative 3: Using direct PersonalAccessToken creation
            $tokenResult = $user->createToken('API Token', ['*']);
            $token = $tokenResult->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token refresh failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's active tokens
     */
    public function tokens(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()->select([
            'id', 'name', 'abilities', 'last_used_at', 'created_at'
        ])->get();

        return response()->json([
            'success' => true,
            'data' => [
                'tokens' => $tokens,
                'total' => $tokens->count()
            ]
        ]);
    }

    /**
     * Revoke specific token by ID
     */
    public function revokeToken(Request $request, $tokenId): JsonResponse
    {
        try {
            $token = $request->user()->tokens()->find($tokenId);
            
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token not found'
                ], 404);
            }

            $token->delete();

            return response()->json([
                'success' => true,
                'message' => 'Token revoked successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token revocation failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
