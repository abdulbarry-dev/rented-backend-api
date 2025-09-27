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
        // User management gates (ADMIN ONLY)
        Gate::define('manage-users', function ($user) {
            // Only super admins can manage users
            return $user instanceof Admin && $user->isSuper() && $user->isActive();
        });
        
        Gate::define('view-all-users', function ($user) {
            // Only super admins can view all users
            return $user instanceof Admin && $user->isSuper() && $user->isActive();
        });
        
        // Profile management (Users can manage their own profiles)
        Gate::define('manage-own-profile', function ($user, $targetUser = null) {
            // Users can manage their own profile only
            if ($user instanceof User && $targetUser instanceof User) {
                return $user->id === $targetUser->id;
            }
            // Admins can manage any profile
            if ($user instanceof Admin && $targetUser instanceof User) {
                return $user->isSuper() && $user->isActive();
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
