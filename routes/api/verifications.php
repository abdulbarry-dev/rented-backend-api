<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserVerificationController;

/**
 * User Verification Routes
 * 
 * Routes for user verification management
 */

// Protected verification routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/verification/status', [UserVerificationController::class, 'status']);
    Route::get('/verification/requirements', [UserVerificationController::class, 'requirements']);
    Route::post('/verification/submit', [UserVerificationController::class, 'submit']);
    Route::post('/verification/resubmit', [UserVerificationController::class, 'resubmit']);
});
