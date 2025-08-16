<?php

namespace App\Http\Controllers;

use App\Models\Candidato;
use App\Models\Colaborador;
use App\Models\Candidatura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ColaboradorAreaController extends Controller
{
    /**
     * Obter dados do colaborador logado
     */
    public function me(Request $request)
    {
        try {
            $candidato = $request->user();
            Log::info('Candidato logado:', ['id' => $candidato->id, 'email' => $candidato->email]);
            
            // Buscar todos os vínculos de colaborador deste candidato
            $colaboradores = Colaborador::whereHas('candidatura', function($query) use ($candidato) {
                $query->where('candidato_id', $candidato->id);
            })
            ->with(['company', 'candidatura.oportunidade'])
            ->orderBy('data_inicio_contrato', 'desc')
            ->get();
            
            Log::info('Colaboradores encontrados:', ['count' => $colaboradores->count()]);
            
            if ($colaboradores->isEmpty()) {
                Log::warning('Nenhum colaborador encontrado para o candidato', ['candidato_id' => $candidato->id]);
                return response()->json([
                    'message' => 'Nenhum vínculo de colaborador encontrado.'
                ], 404);
            }
            
            return response()->json([
                'candidato' => [
                    'id' => $candidato->id,
                    'nome' => $candidato->nome,
                    'apelido' => $candidato->apelido,
                    'email' => $candidato->email,
                    'nome_completo' => $candidato->nome_completo,
                ],
                'colaboradores' => $colaboradores->map(function($colaborador) {
                    return [
                        'id' => $colaborador->id,
                        'empresa' => [
                            'id' => $colaborador->company->id,
                            'nome' => $colaborador->company->name,
                            'logo_url' => $colaborador->company->logo_url,
                        ],
                        'posicao' => $colaborador->posicao,
                        'departamento' => $colaborador->departamento,
                        'vencimento' => $colaborador->vencimento,
                        'data_inicio_contrato' => $colaborador->data_inicio_contrato,
                        'data_fim_contrato' => $colaborador->data_fim_contrato,
                        'contrato_ativo' => $colaborador->isContratoAtivo(),
                        'sem_termo' => $colaborador->isContratoSemTermo(),
                        'oportunidade_origem' => [
                            'id' => $colaborador->candidatura->oportunidade->id,
                            'titulo' => $colaborador->candidatura->oportunidade->titulo,
                            'slug' => $colaborador->candidatura->oportunidade->slug,
                        ],
                    ];
                })
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados do colaborador: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
    
    /**
     * Dashboard do colaborador
     */
    public function dashboard(Request $request)
    {
        try {
            $candidato = $request->user();
            
            // Buscar colaboradores ativos
            $colaboradoresAtivos = Colaborador::whereHas('candidatura', function($query) use ($candidato) {
                $query->where('candidato_id', $candidato->id);
            })
            ->with(['company', 'candidatura.oportunidade'])
            ->get()
            ->filter(function($colaborador) {
                return $colaborador->isContratoAtivo();
            });
            
            // Buscar todo o histórico de candidaturas
            $totalCandidaturas = Candidatura::where('candidato_id', $candidato->id)->count();
            
            // Buscar candidaturas contratadas
            $candidaturasContratadas = Candidatura::where('candidato_id', $candidato->id)
                ->whereHas('colaborador')
                ->count();
            
            // Calcular taxa de sucesso
            $taxaSucesso = $totalCandidaturas > 0 ? 
                round(($candidaturasContratadas / $totalCandidaturas) * 100, 1) : 0;
            
            return response()->json([
                'estatisticas' => [
                    'total_candidaturas' => $totalCandidaturas,
                    'candidaturas_contratadas' => $candidaturasContratadas,
                    'taxa_sucesso' => $taxaSucesso,
                    'contratos_ativos' => $colaboradoresAtivos->count(),
                ],
                'contratos_ativos' => $colaboradoresAtivos->map(function($colaborador) {
                    return [
                        'id' => $colaborador->id,
                        'empresa' => $colaborador->company->name,
                        'posicao' => $colaborador->posicao,
                        'departamento' => $colaborador->departamento,
                        'data_inicio' => $colaborador->data_inicio_contrato,
                        'data_fim' => $colaborador->data_fim_contrato,
                        'sem_termo' => $colaborador->isContratoSemTermo(),
                    ];
                }),
                'perfil_candidato' => [
                    'nome' => $candidato->nome_completo,
                    'email' => $candidato->email,
                    'skills' => $candidato->skills ?? [],
                    'experiencia_profissional' => $candidato->experiencia_profissional ?? [],
                ]
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar dashboard do colaborador: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
    
    /**
     * Histórico de candidaturas do colaborador
     */
    public function historicoContratos(Request $request)
    {
        try {
            $candidato = $request->user();
            
            // Buscar todos os contratos (ativos e inativos)
            $contratos = Colaborador::whereHas('candidatura', function($query) use ($candidato) {
                $query->where('candidato_id', $candidato->id);
            })
            ->with(['company', 'candidatura.oportunidade'])
            ->orderBy('data_inicio_contrato', 'desc')
            ->get();
            
            return response()->json([
                'contratos' => $contratos->map(function($colaborador) {
                    return [
                        'id' => $colaborador->id,
                        'empresa' => [
                            'id' => $colaborador->company->id,
                            'nome' => $colaborador->company->name,
                            'logo_url' => $colaborador->company->logo_url,
                        ],
                        'posicao' => $colaborador->posicao,
                        'departamento' => $colaborador->departamento,
                        'vencimento' => $colaborador->vencimento,
                        'data_inicio_contrato' => $colaborador->data_inicio_contrato,
                        'data_fim_contrato' => $colaborador->data_fim_contrato,
                        'contrato_ativo' => $colaborador->isContratoAtivo(),
                        'sem_termo' => $colaborador->isContratoSemTermo(),
                        'oportunidade_origem' => [
                            'id' => $colaborador->candidatura->oportunidade->id,
                            'titulo' => $colaborador->candidatura->oportunidade->titulo,
                            'slug' => $colaborador->candidatura->oportunidade->slug,
                        ],
                        'candidatura_data' => $colaborador->candidatura->created_at,
                    ];
                })
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar histórico de contratos: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
    
    /**
     * Verificar se o candidato é colaborador
     */
    public function verificarAcesso(Request $request)
    {
        try {
            $candidato = $request->user();
            
            if (!$candidato) {
                return response()->json([
                    'is_colaborador' => false,
                    'message' => 'Candidato não autenticado'
                ], 401);
            }
            
            // Verificar se tem pelo menos uma candidatura contratada
            $isColaborador = Candidatura::where('candidato_id', $candidato->id)
                ->whereHas('colaborador')
                ->exists();
            
            return response()->json([
                'is_colaborador' => $isColaborador,
                'message' => $isColaborador ? 
                    'Candidato tem acesso à área de colaborador' : 
                    'Candidato não foi contratado por nenhuma empresa'
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Erro ao verificar acesso de colaborador: ' . $e->getMessage());
            return response()->json([
                'is_colaborador' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
}
