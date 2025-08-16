<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureColaboradorAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar se o usuário é candidato e está autenticado
        $candidato = $request->user();
        
        if (!$candidato || !$candidato instanceof \App\Models\Candidato) {
            return response()->json([
                'message' => 'Acesso negado. Apenas candidatos autenticados podem acessar esta área.'
            ], 403);
        }
        
        // Verificar se o candidato tem pelo menos uma candidatura contratada
        $isColaborador = \App\Models\Candidatura::where('candidato_id', $candidato->id)
            ->whereHas('colaborador')
            ->exists();
        
        if (!$isColaborador) {
            return response()->json([
                'message' => 'Acesso negado. Apenas colaboradores contratados podem acessar esta área.'
            ], 403);
        }
        
        return $next($request);
    }
}
