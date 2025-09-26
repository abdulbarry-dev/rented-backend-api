<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;

// Public routes (no authentication required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Admin public routes (no authentication required)
Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminController::class, 'login']);
    Route::get('/debug', [AdminController::class, 'debug']); // Debug endpoint
});

// Test route
Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes - available to all authenticated users
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::post('/refresh-token', [AuthController::class, 'refresh']);
    
    // Token management routes - available to all authenticated users
    Route::get('/tokens', [AuthController::class, 'tokens']);
    Route::delete('/tokens/{tokenId}', [AuthController::class, 'revokeToken']);
    
    // User management routes - RESTRICTED to sellers only
    Route::middleware('can:manage-users')->group(function () {
        Route::apiResource('users', UserController::class);
        
        // Additional user management endpoints
        Route::get('/users/role/{role}', [UserController::class, 'getUsersByRole']);
        Route::patch('/users/{user}/activate', [UserController::class, 'activate']);
        Route::patch('/users/{user}/deactivate', [UserController::class, 'deactivate']);
        Route::patch('/users/{user}/role', [UserController::class, 'changeRole']);
    });
});

// Admin protected routes (admin authentication required)
Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    // Admin auth routes - available to all authenticated admins
    Route::get('/profile', [AdminController::class, 'profile']);
    Route::post('/logout', [AdminController::class, 'logout']);
    Route::post('/logout-all', [AdminController::class, 'logoutAll']);
    
    // Super admin only routes
    Route::middleware('can:super-admin-only')->group(function () {
        Route::post('/register', [AdminController::class, 'register']);
        Route::get('/admins', [AdminController::class, 'index']);
        Route::patch('/admins/{admin}/status', [AdminController::class, 'updateStatus']);
        
        // Full access to all user API endpoints for super admins
        Route::apiResource('users', UserController::class);
        Route::get('/users/role/{role}', [UserController::class, 'getUsersByRole']);
        Route::patch('/users/{user}/activate', [UserController::class, 'activate']);
        Route::patch('/users/{user}/deactivate', [UserController::class, 'deactivate']);
        Route::patch('/users/{user}/role', [UserController::class, 'changeRole']);
    });
});