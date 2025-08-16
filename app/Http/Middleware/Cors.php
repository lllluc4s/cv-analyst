<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Get allowed origins from config
        $allowedOrigins = config('cors.allowed_origins', []);
        $requestOrigin = $request->header('Origin');
        
        // Check if the request origin is allowed
        if ($requestOrigin && in_array($requestOrigin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $requestOrigin);
        } else {
            // Fallback to primary frontend URL
            $frontendUrl = env('APP_FRONTEND_URL') ?: env('FRONTEND_URL', 'http://localhost:5174');
            $response->headers->set('Access-Control-Allow-Origin', $frontendUrl);
        }
        
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        
        return $response;
    }
}
