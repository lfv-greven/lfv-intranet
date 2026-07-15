<?php

namespace App\Providers;

use App\Auth\VereinsfliegerUserProvider;
use App\External\Gotenberg;
use App\Models\User;
use App\Services\VereinsfliegerClient;
use App\Services\VereinsfliegerRequestGate;
use Carbon\Carbon;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Gotenberg::class, fn () => new Gotenberg(
            config('services.gotenberg.url'),
            config('services.gotenberg.username'),
            config('services.gotenberg.password'),
        ));

        $this->app->singleton(VereinsfliegerRequestGate::class, function () {
            return new VereinsfliegerRequestGate(
                Cache::store(config('services.vereinsflieger.rate_limit.cache_store')),
            );
        });

        $this->app->singleton(VereinsfliegerClient::class, function ($app) {
            return new VereinsfliegerClient(
                $app->make(VereinsfliegerRequestGate::class),
                Cache::store(config('services.vereinsflieger.rate_limit.cache_store')),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));
        Number::useLocale('de');

        Auth::provider('vereinsflieger', function ($app, $config) {
            return new VereinsfliegerUserProvider(app('hash'), User::class);
        });

        FilamentColor::register([
            'primary' => '#F65812',
        ]);

        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->isAdmin();
        });

        // Shortcut for a PDF response
        Response::macro('pdf', function ($pdfContent, $filename = 'document.pdf', $inline = true) {
            $disposition = $inline ? 'inline' : 'attachment';

            return Response::make($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "$disposition; filename=\"$filename\"",
            ]);
        });
    }
}
