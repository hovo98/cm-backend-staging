<?php

namespace App\Providers;

use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class VaporUiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->gate();
    }

    /**
     * Register the Vapor UI gate.
     *
     * This gate determines who can access Vapor UI in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewVaporUI', function (User $user = null) {
            return in_array($user?->email, [
                'chaim@financelobby.com',
                'jon@64robots.com',
                'rob@64robots.com',
                'miguel@64robots.com',
                'joel@64robots.com',
                'michael@64robots.com',
            ]);
        });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
}
