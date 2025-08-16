<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Candidato;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class CandidatoAuth
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
        
        $candidato = $accessToken->tokenable;
        
        if (!$candidato instanceof Candidato) {
            return response()->json([
                'message' => 'Acesso negado'
            ], 403);
        }
        
        $request->setUserResolver(function () use ($candidato) {
            return $candidato;
        });
        
        return $next($request);
    }
}
