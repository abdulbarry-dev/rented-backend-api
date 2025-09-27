<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            // User-specific middleware
            'user.auth' => \App\Http\Middleware\UserAuth::class,
            'user.verified' => \App\Http\Middleware\RequireVerification::class,
            
            // Admin-specific middleware
            'admin.auth' => \App\Http\Middleware\AdminAuth::class,
            'admin.role' => \App\Http\Middleware\RequireRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
