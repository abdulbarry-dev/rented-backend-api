<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/**
 * Authentication Routes
 * 
 * Public routes for user authentication
 */

// Public routes (no authentication required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    // User profile and authentication
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::post('/refresh-token', [AuthController::class, 'refresh']);
    
    // Token management
    Route::get('/tokens', [AuthController::class, 'tokens']);
    Route::delete('/tokens/{tokenId}', [AuthController::class, 'revokeToken']);
});
