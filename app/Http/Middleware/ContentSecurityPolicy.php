<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Desativar CSP temporariamente para debug OAuth
        if (env('APP_ENV') === 'local' || env('OAUTH_DEV_MODE', false)) {
            // Em desenvolvimento, usar uma política mais permissiva
            $response->headers->set('Content-Security-Policy', "default-src * 'unsafe-inline' 'unsafe-eval' data: blob:;");
        } else {
            // Em produção, usar política restritiva
            $csp = [
                "default-src 'self'",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://github.com",
                "font-src 'self' https://fonts.gstatic.com https://fonts.googleapis.com",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://github.com",
                "img-src 'self' data: https: https://github.com https://avatars.githubusercontent.com",
                "connect-src 'self' https: https://github.com https://api.github.com",
                "frame-src 'self' https://github.com",
                "object-src 'none'",
                "base-uri 'self'"
            ];
            $response->headers->set('Content-Security-Policy', implode('; ', $csp));
        }

        return $response;
    }
}
