<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            $response = response()->json([
                'message' => 'Unauthenticated',
                'authenticated' => false,
                'login_url' => env('APP_URL') . '/auth/google/redirect'
            ], 401);
            
            // Adicionar headers CORS manualmente para garantir que estejam presentes mesmo em respostas de erro
            $frontendUrl = env('APP_FRONTEND_URL') ?: env('FRONTEND_URL', 'http://localhost:5174');
            $response->headers->set('Access-Control-Allow-Origin', $frontendUrl);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN');
            
            return $response;
        }

        return $next($request);
    }
}
