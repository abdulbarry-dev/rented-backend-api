<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Admin;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // User authorization gates
        Gate::define('manage-users', function ($user) {
            // Allow super admins to manage users
            if ($user instanceof Admin) {
                return $user->isSuper() && $user->isActive();
            }
            // Allow if it's a User model with seller role and active
            if ($user instanceof User) {
                return $user->isSeller() && $user->isActive();
            }
            // Deny for other cases
            return false;
        });
        
        Gate::define('view-all-users', function ($user) {
            // Allow super admins to view all users
            if ($user instanceof Admin) {
                return $user->isSuper() && $user->isActive();
            }
            // Allow if it's a User model with seller role and active
            if ($user instanceof User) {
                return $user->isSeller() && $user->isActive();
            }
            // Deny for other cases
            return false;
        });
        
        Gate::define('manage-own-profile', function ($user, $targetUser = null) {
            // Only allow User models
            if ($user instanceof User && $targetUser instanceof User) {
                return $user->id === $targetUser->id || ($user->isSeller() && $user->isActive());
            }
            return false;
        });

        // Admin authorization gates
        Gate::define('super-admin-only', function ($user) {
            return $user instanceof Admin && $user->isSuper() && $user->isActive();
        });
        
        Gate::define('admin-access', function ($user) {
            return $user instanceof Admin && $user->isActive();
        });
        
        Gate::define('manage-admins', function ($user) {
            return $user instanceof Admin && $user->isSuper() && $user->isActive();
        });
    }
}
