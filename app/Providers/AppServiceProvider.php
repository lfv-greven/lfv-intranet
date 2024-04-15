<?php

namespace App\Providers;

use App\Auth\VereinsfliegerUserProvider;
use App\External\Vereinsflieger;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Vereinsflieger::class, fn () => new Vereinsflieger());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::provider('vereinsflieger', function ($app, $config) {
            return new VereinsfliegerUserProvider(app('hash'), User::class);
        });
    }
}
