<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RefreshTokenCookie
{
    public function handle(Request $request, Closure $next): Response
    {
        // Incoming silent refresh: pull refresh_token from the HttpOnly cookie
        // into the request so Passport's token endpoint receives it.
        if ($request->is('oauth/token') && $request->input('grant_type') === 'refresh_token') {
            $cookie = $request->cookie('refresh_token');
            if ($cookie) {
                $request->merge(['refresh_token' => $cookie]);
                $request->request->set('refresh_token', $cookie);
            }
        }

        $response = $next($request);

        // Outgoing: move refresh_token out of the JSON body into an HttpOnly cookie.
        if ($request->is('oauth/token') && $response->getStatusCode() === 200) {
            $data = json_decode($response->getContent(), true);
            if (is_array($data) && isset($data['refresh_token'])) {
                $refresh = $data['refresh_token'];
                unset($data['refresh_token']);
                $response->setContent(json_encode($data));

                $response->headers->setCookie(cookie(
                    'refresh_token',
                    $refresh,
                    60 * 24 * 30, // 30 days, in minutes
                    '/',
                    null,
                    true,  // Secure
                    true,  // HttpOnly
                    false,
                    'Strict' // SameSite
                ));
            }
        }

        return $response;
    }
}
