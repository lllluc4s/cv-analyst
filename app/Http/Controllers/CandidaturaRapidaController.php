<?php

namespace App\Http\Controllers;

use App\Models\Candidatura;
use App\Models\Oportunidade;
use App\Models\Candidato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CandidaturaRapidaController extends Controller
{
    /**
     * Apply to an opportunity with one click (authenticated candidato).
     */
    public function candidatar(Request $request, $oportunidadeId)
    {
        try {
            $candidato = $request->user();
            
            // Find the opportunity by ID
            $oportunidade = Oportunidade::findOrFail($oportunidadeId);
            
            // Check if candidato already applied to this opportunity
            $existingCandidatura = Candidatura::where('oportunidade_id', $oportunidade->id)
                ->where('candidato_id', $candidato->id)
                ->first();
                
            if ($existingCandidatura) {
                return response()->json([
                    'message' => 'Já se candidatou a esta oportunidade'
                ], 409);
            }
            
            // Create candidatura with candidato's profile data
            $candidatura = Candidatura::create([
                'oportunidade_id' => $oportunidade->id,
                'candidato_id' => $candidato->id,
                'nome' => $candidato->nome,
                'apelido' => $candidato->apelido,
                'email' => $candidato->email,
                'telefone' => $candidato->telefone,
                'cv_path' => $candidato->cv_path,
                'linkedin_url' => $candidato->linkedin_url,
                'rgpd_aceito' => true,
                'consentimento_rgpd' => true,
                'skills' => $candidato->skills ?? [],
            ]);
            
            // Calculate skills score if both candidato and opportunity have skills
            if ($candidato->skills && $oportunidade->skills_desejadas) {
                $candidatura->skills_extraidas = $candidato->skills;
                $candidatura->atualizarPontuacao();
            }
            
            return response()->json([
                'message' => 'Candidatura enviada com sucesso!',
                'candidatura' => [
                    'id' => $candidatura->id,
                    'oportunidade' => [
                        'id' => $oportunidade->id,
                        'titulo' => $oportunidade->titulo,
                        'empresa' => $oportunidade->empresa,
                    ],
                    'pontuacao_skills' => $candidatura->pontuacao_skills,
                    'created_at' => $candidatura->created_at,
                ]
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Quick application error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
    
    /**
     * Check if candidato can apply to an opportunity.
     */
    public function canApply(Request $request, $oportunidadeId)
    {
        try {
            $candidato = $request->user();
            
            // Find the opportunity by ID
            $oportunidade = Oportunidade::findOrFail($oportunidadeId);
            
            // Check if candidato already applied
            $hasApplied = Candidatura::where('oportunidade_id', $oportunidade->id)
                ->where('candidato_id', $candidato->id)
                ->exists();
            
            // Candidato can apply if they haven't applied before
            $canApply = !$hasApplied;
            
            return response()->json([
                'can_apply' => $canApply,
                'has_applied' => $hasApplied,
                'missing_fields' => [],
                'profile_complete' => true,
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Check can apply error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
}
