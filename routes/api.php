<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\UserVerificationController;
use App\Http\Controllers\AdminUserVerificationController;

// Health check endpoint for Railway
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now()->toISOString(),
        'app' => config('app.name'),
        'version' => '1.0.0'
    ]);
});

// Public routes (no authentication required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Debug register route
Route::match(['GET', 'POST'], '/register-debug', function () {
    return response()->json([
        'method' => request()->method(),
        'url' => request()->url(),
        'headers' => request()->headers->all(),
        'content' => request()->getContent(),
        'all_data' => request()->all(),
    ]);
});

// Admin public routes (no authentication required)
Route::prefix('admin')->group(function () {
    Route::post('/register', [AdminController::class, 'register']); // Anyone can register as admin
    Route::post('/login', [AdminController::class, 'login']);
    Route::get('/debug', [AdminController::class, 'debug']); // Debug endpoint
});

// Test route
Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

// POST Test route
Route::post('/test-post', function () {
    return response()->json(['message' => 'POST is working', 'data' => request()->all()]);
});

// Debug route for Railway
Route::get('/debug', function () {
    return response()->json([
        'message' => 'Debug endpoint working',
        'app_key' => config('app.key') ? 'SET' : 'MISSING',
        'db_connection' => config('database.default'),
        'environment' => config('app.env'),
        'routes_cached' => file_exists(base_path('bootstrap/cache/routes-v7.php')),
        'config_cached' => file_exists(base_path('bootstrap/cache/config.php')),
    ]);
});

// Public product routes (no authentication required)
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

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
    
    // Product management routes - available to all authenticated users
    Route::get('/my-products', [ProductController::class, 'myProducts']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    
    // User verification routes - available to all authenticated users
    Route::get('/verification/status', [UserVerificationController::class, 'status']);
    Route::get('/verification/requirements', [UserVerificationController::class, 'requirements']);
    Route::post('/verification/submit', [UserVerificationController::class, 'submit']);
    Route::post('/verification/resubmit', [UserVerificationController::class, 'resubmit']);
    
    // Note: User management is now exclusively handled by admins via /api/admin/* routes
});

// Admin protected routes (admin authentication required)
Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    // Admin auth routes - available to all authenticated admins
    Route::get('/profile', [AdminController::class, 'profile']);
    Route::post('/logout', [AdminController::class, 'logout']);
    Route::post('/logout-all', [AdminController::class, 'logoutAll']);
    
    // Super admin only routes
    Route::middleware('can:super-admin-only')->group(function () {
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