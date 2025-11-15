<?php

use Illuminate\Support\Facades\Route;

/**
 * Main API Routes File
 * 
 * This file serves as the entry point for all API routes.
 * Individual route groups are organized in the routes/api/ directory.
 */

// Health check endpoint for Railway
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now()->toISOString(),
        'app' => config('app.name'),
        'version' => '1.0.0'
    ]);
});

// Test route
Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

/**
 * Load Authentication Routes
 * Handles user registration, login, profile, and token management
 */
require __DIR__ . '/api/auth.php';

/**
 * Load Product Routes
 * Handles public and authenticated product operations
 */
require __DIR__ . '/api/products.php';

/**
 * Load User Verification Routes
 * Handles user verification submission and status
 */
require __DIR__ . '/api/verifications.php';

/**
 * Load Admin Routes
 * Handles all admin operations including admin auth, user management,
 * product review, and verification review
 */
Route::prefix('admin')->group(function () {
    require __DIR__ . '/api/admin.php';
});