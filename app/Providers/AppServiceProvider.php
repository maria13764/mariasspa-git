<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::define('manage-users', function (User $user): bool {
            return $user->role === UserRole::Admin;
        });

        Gate::define('manage-appointments', function (User $user): bool {
            return $user->role === UserRole::Admin;
        });

        Gate::define('book-appointments', function (User $user): bool {
            return $user->role === UserRole::Client;
        });

        Gate::define('manage-therapist-appointments', function (User $user): bool {
            return $user->role === UserRole::Therapist;
        });
    }
}
