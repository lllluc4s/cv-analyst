<?php

namespace App\Http\Controllers;

use App\Models\Candidato;
use App\Models\Oportunidade;
use App\Models\Candidatura;
use App\Models\ConviteCandidato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ConviteCandidatura;

class CandidatosPotencialController extends Controller
{
    /**
     * Buscar candidatos com potencial para uma oportunidade específica
     */
    public function buscarCandidatosPotencial(Request $request, $oportunidadeId)
    {
        try {
            $company = $request->user();
            
            // Verificar se a oportunidade pertence à empresa
            $oportunidade = $company->oportunidades()->findOrFail($oportunidadeId);
            
            // Buscar candidatos que ainda não se candidataram a esta oportunidade
            $candidatosJaCandidatados = Candidatura::where('oportunidade_id', $oportunidadeId)
                ->pluck('candidato_id')
                ->filter()
                ->toArray();
            
            // Buscar todos os candidatos disponíveis (com email verificado, que não se candidataram e que são visíveis)
            $candidatosPotencial = Candidato::whereNotIn('id', $candidatosJaCandidatados)
                ->where('email_verified_at', '!=', null)
                ->where('is_searchable', true) // Filtrar apenas candidatos visíveis para empresas
                ->get()
                ->map(function ($candidato) use ($oportunidade) {
                    // Extrair skills do candidato (pode estar em JSON ou array)
                    $skillsCandidato = $this->extrairSkillsCandidato($candidato);
                    
                    // Calcular afinidade
                    $afinidade = $this->calcularAfinidade($skillsCandidato, $oportunidade->skills_desejadas);
                    
                    return [
                        'id' => $candidato->id,
                        'nome' => $candidato->nome_completo,
                        'email' => $candidato->email,
                        'skills' => $skillsCandidato,
                        'skills_principais' => $this->getSkillsPrincipais($skillsCandidato, $oportunidade->skills_desejadas),
                        'afinidade_percentual' => $afinidade,
                        'cv_path' => $candidato->cv_path,
                        'linkedin_url' => $candidato->linkedin_url,
                    ];
                })
                ->where('afinidade_percentual', '>', 0) // Apenas candidatos com alguma afinidade
                ->sortByDesc('afinidade_percentual')
                ->take(50) // Aumentar para 50 candidatos
                ->values();
            
            return response()->json([
                'oportunidade' => [
                    'id' => $oportunidade->id,
                    'titulo' => $oportunidade->titulo,
                    'skills_desejadas' => $oportunidade->skills_desejadas,
                ],
                'candidatos' => $candidatosPotencial
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar candidatos potencial: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Convidar candidato para uma oportunidade
     */
    public function convidarCandidato(Request $request)
    {
        try {
            $request->validate([
                'candidato_id' => 'required|exists:candidatos,id',
                'oportunidade_id' => 'required|exists:oportunidades,id',
                'mensagem_personalizada' => 'nullable|string|max:1000'
            ]);
            
            $company = $request->user();
            
            // Verificar se a oportunidade pertence à empresa
            $oportunidade = $company->oportunidades()->findOrFail($request->oportunidade_id);
            $candidato = Candidato::findOrFail($request->candidato_id);
            
            // Verificar se o candidato já foi convidado para esta oportunidade
            $conviteExistente = ConviteCandidato::where('candidato_id', $candidato->id)
                ->where('oportunidade_id', $oportunidade->id)
                ->first();
                
            if ($conviteExistente) {
                return response()->json([
                    'message' => 'Este candidato já foi convidado para esta oportunidade.'
                ], 409);
            }
            
            // Verificar se o candidato já se candidatou
            $candidaturaExistente = Candidatura::where('candidato_id', $candidato->id)
                ->where('oportunidade_id', $oportunidade->id)
                ->first();
                
            if ($candidaturaExistente) {
                return response()->json([
                    'message' => 'Este candidato já se candidatou a esta oportunidade.'
                ], 409);
            }
            
            // Criar o registo do convite
            $convite = ConviteCandidato::create([
                'candidato_id' => $candidato->id,
                'oportunidade_id' => $oportunidade->id,
                'company_id' => $company->id,
                'mensagem_personalizada' => $request->mensagem_personalizada,
                'token' => \Illuminate\Support\Str::random(32),
                'enviado_em' => now(),
            ]);
            
            // Enviar email de convite
            $linkCandidatura = config('app.frontend_url') . '/oportunidade/' . $oportunidade->slug . '?convite=' . $convite->token;
            
            Mail::to($candidato->email)->send(new ConviteCandidatura(
                $candidato,
                $oportunidade,
                $company,
                $linkCandidatura,
                $request->mensagem_personalizada
            ));
            
            return response()->json([
                'message' => 'Convite enviado com sucesso!',
                'convite' => [
                    'id' => $convite->id,
                    'candidato_nome' => $candidato->nome_completo,
                    'enviado_em' => $convite->enviado_em,
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao convidar candidato: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Listar histórico de convites enviados para uma oportunidade
     */
    public function historicoConvites(Request $request, $oportunidadeId)
    {
        try {
            $company = $request->user();
            
            // Verificar se a oportunidade pertence à empresa
            $oportunidade = $company->oportunidades()->findOrFail($oportunidadeId);
            
            $convites = ConviteCandidato::where('oportunidade_id', $oportunidadeId)
                ->with('candidato:id,nome,apelido,email')
                ->orderBy('enviado_em', 'desc')
                ->get()
                ->map(function ($convite) {
                    return [
                        'id' => $convite->id,
                        'candidato' => [
                            'nome' => $convite->candidato->nome_completo,
                            'email' => $convite->candidato->email,
                        ],
                        'enviado_em' => $convite->enviado_em,
                        'visualizado_em' => $convite->visualizado_em,
                        'candidatou_se' => $convite->candidatou_se,
                    ];
                });
            
            return response()->json([
                'oportunidade' => [
                    'id' => $oportunidade->id,
                    'titulo' => $oportunidade->titulo,
                ],
                'convites' => $convites
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar histórico de convites: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
    
    /**
     * Rastrear visualização de convite
     */
    public function trackConviteVisualizado($token)
    {
        try {
            $convite = ConviteCandidato::where('token', $token)->first();
            
            if ($convite && !$convite->visualizado_em) {
                $convite->update(['visualizado_em' => now()]);
            }
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao rastrear visualização de convite: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }
    
    /**
     * Calcular a afinidade entre as skills do candidato e as skills desejadas da oportunidade
     */
    private function calcularAfinidade($skillsCandidato, $skillsDesejadas)
    {
        if (empty($skillsCandidato) || empty($skillsDesejadas)) {
            return 0;
        }
        
        // Normalizar skills do candidato para comparação
        $skillsCandidatoNorm = array_map(function($skill) {
            return $this->normalizarSkill($skill);
        }, $skillsCandidato);
        
        $pontuacaoTotal = 0;
        $pesoTotal = 0;
        
        // Para cada skill desejada, verificar se o candidato possui
        foreach ($skillsDesejadas as $skillDesejada) {
            $nomeSkill = is_array($skillDesejada) ? $skillDesejada['nome'] : $skillDesejada;
            $pesoSkill = is_array($skillDesejada) && isset($skillDesejada['peso']) ? $skillDesejada['peso'] : 1;
            
            $nomeSkillNorm = $this->normalizarSkill($nomeSkill);
            $pesoTotal += $pesoSkill;
            
            // Verificar match exato
            if (in_array($nomeSkillNorm, $skillsCandidatoNorm)) {
                $pontuacaoTotal += $pesoSkill;
            }
            // Verificar match parcial (para skills compostas)
            else {
                foreach ($skillsCandidatoNorm as $skillCandidato) {
                    if ($this->verificarMatchParcial($skillCandidato, $nomeSkillNorm)) {
                        $pontuacaoTotal += $pesoSkill * 0.7; // 70% do peso para match parcial
                        break;
                    }
                }
            }
        }
        
        // Calcular percentual de afinidade
        return $pesoTotal > 0 ? round(($pontuacaoTotal / $pesoTotal) * 100, 1) : 0;
    }
    
    /**
     * Extrair skills do candidato de forma robusta
     */
    private function extrairSkillsCandidato($candidato)
    {
        $skills = [];
        
        // Se já é um array, usar diretamente
        if (is_array($candidato->skills)) {
            $skills = $candidato->skills;
        }
        // Se é string JSON, decodificar
        elseif (is_string($candidato->skills)) {
            $decoded = json_decode($candidato->skills, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $skills = $decoded;
            }
        }
        
        // Filtrar skills vazias e duplicadas
        $skills = array_filter($skills, function($skill) {
            return !empty(trim($skill));
        });
        
        return array_unique($skills);
    }
    
    /**
     * Normalizar skill para comparação
     */
    private function normalizarSkill($skill)
    {
        // Converter para minúsculas, remover espaços extras e caracteres especiais
        $normalized = strtolower(trim($skill));
        $normalized = preg_replace('/[^a-z0-9\s+#.-]/', '', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        
        return trim($normalized);
    }
    
    /**
     * Verificar match parcial entre skills
     */
    private function verificarMatchParcial($skillCandidato, $skillDesejada)
    {
        // Se uma skill contém a outra
        if (strpos($skillCandidato, $skillDesejada) !== false || 
            strpos($skillDesejada, $skillCandidato) !== false) {
            return true;
        }
        
        // Verificar palavras-chave comuns
        $palavrasCandidato = explode(' ', $skillCandidato);
        $palavrasDesejada = explode(' ', $skillDesejada);
        
        foreach ($palavrasCandidato as $palavra) {
            if (strlen($palavra) > 2 && in_array($palavra, $palavrasDesejada)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Obter as skills principais que fazem match
     */
    private function getSkillsPrincipais($skillsCandidato, $skillsDesejadas)
    {
        if (empty($skillsCandidato) || empty($skillsDesejadas)) {
            return [];
        }
        
        $skillsComuns = [];
        $skillsCandidatoNorm = array_map([$this, 'normalizarSkill'], $skillsCandidato);
        
        foreach ($skillsDesejadas as $skillDesejada) {
            $nomeSkill = is_array($skillDesejada) ? $skillDesejada['nome'] : $skillDesejada;
            $pesoSkill = is_array($skillDesejada) && isset($skillDesejada['peso']) ? $skillDesejada['peso'] : 1;
            $nomeSkillNorm = $this->normalizarSkill($nomeSkill);
            
            // Verificar match exato
            if (in_array($nomeSkillNorm, $skillsCandidatoNorm)) {
                $skillsComuns[] = [
                    'nome' => $nomeSkill,
                    'peso' => $pesoSkill,
                    'match_tipo' => 'exato'
                ];
            }
            // Verificar match parcial
            else {
                foreach ($skillsCandidato as $skillCandidato) {
                    $skillCandidatoNorm = $this->normalizarSkill($skillCandidato);
                    if ($this->verificarMatchParcial($skillCandidatoNorm, $nomeSkillNorm)) {
                        $skillsComuns[] = [
                            'nome' => $nomeSkill,
                            'peso' => $pesoSkill,
                            'match_tipo' => 'parcial',
                            'skill_candidato' => $skillCandidato
                        ];
                        break;
                    }
                }
            }
        }
        
        // Ordenar por peso (mais importantes primeiro), depois por tipo de match
        usort($skillsComuns, function($a, $b) {
            if ($a['peso'] == $b['peso']) {
                return $a['match_tipo'] === 'exato' ? -1 : 1;
            }
            return $b['peso'] <=> $a['peso'];
        });
        
        return array_slice($skillsComuns, 0, 5); // Máximo 5 skills principais
    }
}
