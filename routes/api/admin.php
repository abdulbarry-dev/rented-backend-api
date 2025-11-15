<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\AdminUserVerificationController;

/**
 * Admin Routes
 * 
 * All admin-related routes including authentication and management
 */

// Admin public routes (no authentication required)
Route::post('/register', [AdminController::class, 'register']); // Anyone can register as admin
Route::post('/login', [AdminController::class, 'login']);

// Admin protected routes (admin authentication required)
Route::middleware('auth:sanctum')->group(function () {
    // Admin auth routes - available to all authenticated admins
    Route::get('/profile', [AdminController::class, 'profile']);
    Route::post('/logout', [AdminController::class, 'logout']);
    Route::post('/logout-all', [AdminController::class, 'logoutAll']);
    
    // Super admin only routes
    Route::middleware('can:super-admin-only')->group(function () {
        // Admin statistics
        Route::get('/admins', [AdminController::class, 'index']);
        Route::get('/statistics', [AdminController::class, 'statistics']);
        
        // Admin approval and management
        Route::get('/admins/pending', [AdminController::class, 'pendingAdmins']);
        Route::patch('/admins/{admin}/approve', [AdminController::class, 'approveAdmin']);
        Route::patch('/admins/{admin}/reject', [AdminController::class, 'rejectAdmin']);
        Route::patch('/admins/{admin}/ban', [AdminController::class, 'banAdmin']);
        Route::patch('/admins/{admin}/unban', [AdminController::class, 'unbanAdmin']);
        Route::delete('/admins/{admin}', [AdminController::class, 'deleteAdmin']);
        
        // User management - ADMIN ONLY (super admins have full access)
        Route::apiResource('users', UserController::class);
        Route::get('/users/role/{role}', [UserController::class, 'getUsersByRole']);
        Route::patch('/users/{user}/activate', [UserController::class, 'activate']);
        Route::patch('/users/{user}/deactivate', [UserController::class, 'deactivate']);
        Route::patch('/users/{user}/role', [UserController::class, 'changeRole']);
        
        // Product management - ADMIN ONLY (super admins can review products)
        Route::get('/products/pending', [AdminProductController::class, 'pendingProducts']);
        Route::get('/products/all', [AdminProductController::class, 'allProducts']);
        Route::patch('/products/{product}/review', [AdminProductController::class, 'reviewProduct']);
        
        // User verification management - ADMIN ONLY (super admins can review user verifications)
        Route::get('/verifications/pending', [AdminUserVerificationController::class, 'pendingVerifications']);
        Route::get('/verifications/all', [AdminUserVerificationController::class, 'allVerifications']);
        Route::get('/verifications/statistics', [AdminUserVerificationController::class, 'statistics']);
        Route::patch('/verifications/{verification}/review', [AdminUserVerificationController::class, 'reviewVerification']);
    });
});
