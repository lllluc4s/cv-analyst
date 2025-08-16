<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FilterSearchableCandidatos
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Este middleware pode ser usado para garantir que apenas candidatos
        // que optaram por ser pesquisáveis sejam incluídos em buscas de empresas
        
        // Por exemplo, se tivéssemos uma rota de busca de candidatos para empresas:
        // $request->merge(['searchable_only' => true]);
        
        return $next($request);
    }
}
