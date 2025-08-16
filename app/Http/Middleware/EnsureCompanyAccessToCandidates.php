<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Candidatura;
use App\Models\Oportunidade;

class EnsureCompanyAccessToCandidates
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $company = $request->user();
        
        if (!$company) {
            return response()->json(['message' => 'Não autorizado'], 401);
        }

        // Para rotas de email de estados, não precisamos verificar acesso específico
        // pois o estado pode ser usado por qualquer empresa
        $path = $request->path();
        if (str_contains($path, '/email/') || str_contains($path, '/email')) {
            return $next($request);
        }

        // Se há parâmetro de candidatura_id na rota ou request
        $candidaturaId = $request->route('candidatura_id') 
                      ?? $request->route('id') 
                      ?? $request->input('candidatura_id');

        if ($candidaturaId) {
            $candidatura = Candidatura::find($candidaturaId);
            
            if (!$candidatura) {
                return response()->json(['message' => 'Candidatura não encontrada'], 404);
            }

            // Verificar se a oportunidade pertence à empresa ou não tem company_id (dados legados)
            $oportunidade = Oportunidade::where('id', $candidatura->oportunidade_id)
                ->where(function($q) use ($company) {
                    $q->where('company_id', $company->id)
                      ->orWhereNull('company_id'); // Permitir oportunidades sem company_id
                })
                ->first();

            if (!$oportunidade) {
                return response()->json(['message' => 'Acesso negado a esta candidatura'], 403);
            }
        }

        // Se há parâmetro de oportunidade_id
        $oportunidadeId = $request->route('oportunidade_id') 
                       ?? $request->route('id') 
                       ?? $request->input('oportunidade_id');

        if ($oportunidadeId && !$candidaturaId) {
            $oportunidade = Oportunidade::where('id', $oportunidadeId)
                ->where(function($q) use ($company) {
                    $q->where('company_id', $company->id)
                      ->orWhereNull('company_id');
                })
                ->first();

            if (!$oportunidade) {
                return response()->json(['message' => 'Acesso negado a esta oportunidade'], 403);
            }
        }

        return $next($request);
    }
}
