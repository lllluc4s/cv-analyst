<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleOptions
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Responder imediatamente a requisições OPTIONS com os headers CORS adequados
        if ($request->isMethod('OPTIONS')) {
            $frontendUrl = env('APP_FRONTEND_URL') ?: env('FRONTEND_URL', 'http://localhost:5174');
            
            $response = new Response('', 200);
            $response->headers->set('Access-Control-Allow-Origin', $frontendUrl);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Max-Age', '86400'); // 24 horas

            return $response;
        }

        return $next($request);
    }
}
