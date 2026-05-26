<?php

namespace App\Providers;

use App\Models\PassportClient;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Passport::useClientModel(PassportClient::class);

        // First-party SPA clients skip the consent screen, but the authorize controller
        // type-hints AuthorizationViewResponse, so the binding must still be resolvable.
        Passport::authorizationView('passport::authorize');

        Passport::tokensExpireIn(now()->addMinutes(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
    }
}
