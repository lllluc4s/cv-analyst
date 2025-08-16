<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\Company;
use App\Models\Candidato;
use Symfony\Component\HttpFoundation\Response;

class MultiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'message' => 'Token de autenticação necessário'
            ], 401);
        }
        
        $accessToken = PersonalAccessToken::findToken($token);
        
        if (!$accessToken) {
            return response()->json([
                'message' => 'Token inválido'
            ], 401);
        }
        
        $user = $accessToken->tokenable;
        
        if (!($user instanceof Company) && !($user instanceof Candidato)) {
            return response()->json([
                'message' => 'Tipo de usuário inválido'
            ], 401);
        }
        
        // Set the authenticated user
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        return $next($request);
    }
}
