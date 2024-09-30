<?php

namespace App\Providers;

use App\Auth\VereinsfliegerUserProvider;
use App\External\Vereinsflieger;
use App\Models\User;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Vereinsflieger::class, fn () => new Vereinsflieger);
        $this->app->singleton('vfadmin', function () {
            $vf = new Vereinsflieger;
            $vf->SignIn(
                config('services.vereinsflieger.username'),
                config('services.vereinsflieger.password'),
            );

            return $vf;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::provider('vereinsflieger', function ($app, $config) {
            return new VereinsfliegerUserProvider(app('hash'), User::class);
        });

        FilamentColor::register([
            'primary' => '#F65812',
        ]);
    }
}
