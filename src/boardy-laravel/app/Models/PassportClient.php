<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Passport\Client as BaseClient;

class PassportClient extends BaseClient
{
    public function skipsAuthorization(Authenticatable $user, array $scopes): bool
    {
        // First-party public SPA clients (PKCE, no secret) are trusted — skip the consent screen.
        return $this->secret === null;
    }
}
