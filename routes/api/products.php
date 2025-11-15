<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

/**
 * Product Routes
 * 
 * Public and authenticated product management routes
 */

// Public product routes (no authentication required)
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

// Protected product routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my-products', [ProductController::class, 'myProducts']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
});
