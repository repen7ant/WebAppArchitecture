<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // Принудительно генерируем HTTPS-ссылки, так как мы за Cloudflare
        if ($this->app->environment('production') || $this->app->environment('local')) {
            URL::forceScheme('https');
        }

        User::observe(UserObserver::class);
    }
}
