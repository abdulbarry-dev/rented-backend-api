<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;

// Public routes (no authentication required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Test route
Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

// Public user routes (for testing - you can remove these in production)
Route::get('/users/public', [UserController::class, 'index']); // Public access to view users
Route::get('/users/public/{user}', [UserController::class, 'show']); // Public access to view single user

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::post('/refresh-token', [AuthController::class, 'refresh']);
    
    // Token management routes
    Route::get('/tokens', [AuthController::class, 'tokens']);
    Route::delete('/tokens/{tokenId}', [AuthController::class, 'revokeToken']);
    
    // Protected user management routes (full CRUD)
    Route::apiResource('users', UserController::class);
});