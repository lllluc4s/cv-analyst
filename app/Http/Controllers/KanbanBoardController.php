<?php

namespace App\Http\Controllers;

use App\Jobs\SendCandidateStateEmail;
use App\Models\Candidatura;
use App\Models\BoardState;
use App\Models\KanbanStage;
use App\Models\KanbanStageSetting;
use App\Models\KanbanTransition;
use App\Models\KanbanNote;
use App\Models\Oportunidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class KanbanBoardController extends Controller
{
    use AuthorizesRequests;
    /**
     * Buscar board de uma oportunidade específica
     */
    public function getBoardByOportunidade(Request $request, $oportunidadeSlugOrId)
    {
        try {
            $company = $request->user();
            
            // Verificar se o parâmetro é um ID ou slug
            if (is_numeric($oportunidadeSlugOrId)) {
                // Buscar por ID
                $oportunidade = $company->oportunidades()->findOrFail($oportunidadeSlugOrId);
            } else {
                // Buscar por slug
                $oportunidade = $company->oportunidades()->where('slug', $oportunidadeSlugOrId)->firstOrFail();
            }
            
            // Buscar estados específicos da oportunidade + estados padrão
            // Usar os novos modelos de KanbanStage
            $customStages = KanbanStage::where('oportunidade_id', $oportunidade->id)
                ->with('settings')
                ->ordered()
                ->get();
                
            $defaultStages = KanbanStage::where('is_default', true)
                ->with('settings')
                ->ordered()
                ->get();
            
            // Prioritizar estados personalizados sobre os padrão
            $stages = collect();
            $usedOrders = collect();
            
            // Primeiro adicionar todos os estados personalizados
            foreach ($customStages as $customStage) {
                $stages->push($customStage);
                $usedOrders->push($customStage->order);
            }
            
            // Depois adicionar estados padrão apenas se não há personalizado com a mesma ordem
            foreach ($defaultStages as $defaultStage) {
                if (!$usedOrders->contains($defaultStage->order)) {
                    $stages->push($defaultStage);
                }
            }
            
            // Ordenar por ordem final
            $stages = $stages->sortBy('order');
            
            // Buscar candidaturas da oportunidade 
            $candidaturas = Candidatura::where('oportunidade_id', $oportunidade->id)
                ->with(['stage', 'colaborador'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($candidatura) {
                    $candidatura->is_contratado = $candidatura->colaborador !== null;
                    return $candidatura;
                });
            
            // Organizar candidaturas por estágio
            $board = $stages->map(function ($stage) use ($candidaturas) {
                // Pegar as configurações do estágio
                $settings = $stage->settings ?? null;
                
                return [
                    'id' => $stage->id,
                    'nome' => $stage->name,
                    'cor' => $settings ? $settings->color : '#6B7280',
                    'ordem' => $stage->order,
                    'is_default' => $stage->is_default,
                    'email_enabled' => $settings ? $settings->email_enabled : false,
                    'email_subject' => $settings ? $settings->email_subject : null,
                    'email_body' => $settings ? $settings->email_body : null,
                    'candidaturas' => $candidaturas->filter(function ($candidatura) use ($stage) {
                        // Se não tem estágio definido, colocar no primeiro estágio por padrão
                        return $candidatura->stage_id === $stage->id || 
                               ($candidatura->stage_id === null && $stage->order === 1);
                    })->values()->map(function ($candidatura) {
                        // Carregar as notas do kanban
                        $notes = KanbanNote::where('candidatura_id', $candidatura->id)
                            ->orderBy('created_at', 'desc')
                            ->first();
                            
                        return [
                            'id' => $candidatura->id,
                            'nome' => $candidatura->nome,
                            'email' => $candidatura->email,
                            'telefone' => $candidatura->telefone,
                            'pontuacao_skills' => $candidatura->pontuacao_skills,
                            'created_at' => $candidatura->created_at,
                            'moved_to_state_at' => $candidatura->moved_to_state_at,
                            'cv_path' => $candidatura->cv_path,
                            'linkedin_url' => $candidatura->linkedin_url,
                            'skills_extraidas' => $candidatura->skills_extraidas,
                            'notas_privadas' => $notes ? $notes->content : $candidatura->notas_privadas,
                            'company_rating' => $candidatura->company_rating,
                            'slug' => $candidatura->slug
                        ];
                    })
                ];
            });
            
            return response()->json([
                'oportunidade' => [
                    'id' => $oportunidade->id,
                    'titulo' => $oportunidade->titulo,
                ],
                'board' => $board
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error on KanbanBoardController@getBoardByOportunidade: ' . $e->getMessage(), [
                'oportunidadeSlugOrId' => $oportunidadeSlugOrId,
                'user' => $request->user()->id ?? 'guest',
                'exception' => $e
            ]);
            
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Mover candidatura entre estados
     */
    public function moveCandidatura(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'candidatura_id' => 'required|exists:candidaturas,id',
                'to_stage_id' => 'required|exists:kanban_stages,id',
                'note' => 'nullable|string',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $candidaturaId = $request->candidatura_id;
            $toStageId = $request->to_stage_id;
            $note = $request->note;
            
            $candidatura = Candidatura::with(['stage'])->findOrFail($candidaturaId);
            $toStage = KanbanStage::with(['settings'])->findOrFail($toStageId);
            
            // Salvar o stage_id atual para histórico
            $fromStageId = $candidatura->stage_id;
            
            // Atualizar stage_id e board_state_id (para compatibilidade) na candidatura
            $candidatura->stage_id = $toStageId;
            $candidatura->moved_to_state_at = now();
            
            // Mapeamento para o board_state_id (para compatibilidade)
            $boardState = BoardState::where('nome', $toStage->name)
                                   ->where('ordem', $toStage->order)
                                   ->first();
                                   
            $previousState = $candidatura->boardState; // Guardar estado anterior
            
            if ($boardState) {
                $candidatura->board_state_id = $boardState->id;
            }
            
            $candidatura->save();
            
            // Verificar se foi movido para "Contratado" e disparar evento
            if ($boardState && strtolower($boardState->nome) === 'contratado') {
                event(new \App\Events\CandidaturaMovedToContratado($candidatura, $previousState, $boardState));
            }
            
            // Registrar transição no histórico
            $transition = new KanbanTransition([
                'candidatura_id' => $candidaturaId,
                'from_stage_id' => $fromStageId,
                'to_stage_id' => $toStageId,
                'note' => $note,
                'email_sent' => false,
            ]);
            
            $transition->save();
            
            // Se o estágio tem configuração de email habilitada, disparar o job para envio
            $settings = $toStage->settings;
            
            // Log detalhado para debug
            Log::info('Verificando configurações de email para estágio', [
                'stage_id' => $toStageId,
                'stage_name' => $toStage->name,
                'has_settings' => $settings ? true : false,
                'email_enabled' => $settings ? $settings->email_enabled : null,
                'email_subject' => $settings ? $settings->email_subject : null,
                'email_body' => $settings ? ($settings->email_body ? 'presente' : 'ausente') : null,
                'candidatura_id' => $candidaturaId,
                'candidato_nome' => $candidatura->nome,
                'candidato_email' => $candidatura->email,
                'consentimento_rgpd' => $candidatura->consentimento_rgpd,
                'pode_ser_contactado' => $candidatura->pode_ser_contactado,
                'consentimento_emails' => $candidatura->consentimento_emails ?? 'não definido'
            ]);
            
            if ($settings && $settings->email_enabled && $settings->email_subject && $settings->email_body) {
                // Verificar se pode enviar email (consentimento RGPD E consentimento específico para emails)
                if ($candidatura->consentimento_rgpd && $candidatura->pode_ser_contactado && $candidatura->consentimento_emails) {
                    Log::info('Enviando email para candidato - consentimento OK', [
                        'candidato_email' => $candidatura->email,
                        'stage_name' => $toStage->name
                    ]);
                    
                    $emailData = [
                        'subject' => $settings->email_subject,
                        'body' => $settings->email_body,
                        'candidato_nome' => $candidatura->nome,
                        'candidato_email' => $candidatura->email,
                    ];
                    
                    // Registrar dados de email na transição
                    $transition->email_data = $emailData;
                    $transition->save();
                    
                    // Disparar job para envio de email
                    SendCandidateStateEmail::dispatch($candidatura, $emailData, $transition->id);
                } else {
                    Log::info('Email não enviado - sem consentimento', [
                        'candidato_email' => $candidatura->email,
                        'consentimento_rgpd' => $candidatura->consentimento_rgpd,
                        'pode_ser_contactado' => $candidatura->pode_ser_contactado,
                        'consentimento_emails' => $candidatura->consentimento_emails ?? 'não definido'
                    ]);
                }
            } else {
                Log::info('Email não enviado - configuração incompleta', [
                    'has_settings' => $settings ? true : false,
                    'email_enabled' => $settings ? $settings->email_enabled : null,
                    'has_subject' => $settings && $settings->email_subject ? true : false,
                    'has_body' => $settings && $settings->email_body ? true : false
                ]);
            }
            
            return response()->json([
                'message' => 'Candidatura movida com sucesso',
                'candidatura' => $candidatura
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error on KanbanBoardController@moveCandidatura: ' . $e->getMessage(), [
                'candidaturaId' => $request->candidatura_id ?? null,
                'toStageId' => $request->to_stage_id ?? null,
                'user' => $request->user()->id ?? 'guest',
                'exception' => $e
            ]);
            
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Buscar histórico de uma candidatura
     */
    public function getCandidateHistory(Request $request, $candidaturaId)
    {
        try {
            $candidatura = Candidatura::findOrFail($candidaturaId);
            
            // Buscar histórico usando o novo modelo de transições
            $transitions = KanbanTransition::with(['fromStage', 'toStage'])
                ->where('candidatura_id', $candidaturaId)
                ->orderBy('created_at', 'desc')
                ->get();
                
            $history = $transitions->map(function ($transition) {
                return [
                    'id' => $transition->id,
                    'date' => $transition->created_at,
                    'from_stage' => $transition->fromStage ? $transition->fromStage->name : null,
                    'to_stage' => $transition->toStage ? $transition->toStage->name : null,
                    'note' => $transition->note,
                    'email_sent' => $transition->email_sent,
                ];
            });
            
            return response()->json($history);
            
        } catch (\Exception $e) {
            Log::error('Error on KanbanBoardController@getCandidateHistory: ' . $e->getMessage(), [
                'candidaturaId' => $candidaturaId,
                'user' => $request->user()->id ?? 'guest',
                'exception' => $e
            ]);
            
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Atualizar notas privadas de uma candidatura
     */
    public function updateCandidateNotes(Request $request, $candidaturaId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'notes' => 'required|string',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $candidatura = Candidatura::findOrFail($candidaturaId);
            $company = $request->user();
            
            // Salvar notas no sistema novo (KanbanNote)
            $note = new KanbanNote([
                'candidatura_id' => $candidaturaId,
                'oportunidade_id' => $candidatura->oportunidade_id,
                'content' => $request->notes,
                'created_by' => $company->id,
            ]);
            
            $note->save();
            
            // Manter compatibilidade com o sistema antigo
            $candidatura->notas_privadas = $request->notes;
            $candidatura->save();
            
            return response()->json([
                'message' => 'Notas atualizadas com sucesso',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error on KanbanBoardController@updateCandidateNotes: ' . $e->getMessage(), [
                'candidaturaId' => $candidaturaId,
                'user' => $request->user()->id ?? 'guest',
                'exception' => $e
            ]);
            
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
    
    // Métodos adicionais para gestão de estágios
    
    /**
     * Obter todos os estágios (estados) disponíveis
     */
    public function getStates(Request $request)
    {
        try {
            $states = KanbanStage::with('settings')
                ->where('is_default', true)
                ->orderBy('order')
                ->get()
                ->map(function ($stage) {
                    $settings = $stage->settings;
                    return [
                        'id' => $stage->id,
                        'nome' => $stage->name,
                        'cor' => $settings ? $settings->color : '#6B7280',
                        'ordem' => $stage->order,
                        'is_default' => $stage->is_default,
                        'email_enabled' => $settings ? $settings->email_enabled : false,
                        'email_subject' => $settings ? $settings->email_subject : null,
                        'email_body' => $settings ? $settings->email_body : null,
                    ];
                });
                
            return response()->json($states);
            
        } catch (\Exception $e) {
            Log::error('Error on KanbanBoardController@getStates: ' . $e->getMessage(), [
                'user' => $request->user()->id ?? 'guest',
                'exception' => $e
            ]);
            
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
    
    /**
     * Obter estágios (estados) de uma oportunidade específica
     */
    public function getStatesByOportunidade(Request $request, $oportunidadeSlugOrId)
    {
        try {
            // Verificar se o parâmetro é um ID ou slug
            if (is_numeric($oportunidadeSlugOrId)) {
                // Buscar por ID
                $oportunidade = Oportunidade::findOrFail($oportunidadeSlugOrId);
            } else {
                // Buscar por slug
                $oportunidade = Oportunidade::where('slug', $oportunidadeSlugOrId)->firstOrFail();
            }
            
            $customStages = KanbanStage::with('settings')
                ->where('oportunidade_id', $oportunidade->id)
                ->orderBy('order')
                ->get();
                
            $defaultStages = KanbanStage::with('settings')
                ->where('is_default', true)
                ->orderBy('order')
                ->get();
                
            // Prioritizar estados personalizados sobre os padrão
            $stages = collect();
            $usedOrders = collect();
            
            // Primeiro adicionar todos os estados personalizados
            foreach ($customStages as $customStage) {
                $stages->push($customStage);
                $usedOrders->push($customStage->order);
            }
            
            // Depois adicionar estados padrão apenas se não há personalizado com a mesma ordem
            foreach ($defaultStages as $defaultStage) {
                if (!$usedOrders->contains($defaultStage->order)) {
                    $stages->push($defaultStage);
                }
            }
            
            // Ordenar por ordem final e mapear para o formato de resposta
            $result = $stages->sortBy('order')
                ->map(function ($stage) {
                    $settings = $stage->settings;
                    return [
                        'id' => $stage->id,
                        'nome' => $stage->name,
                        'cor' => $settings ? $settings->color : '#6B7280',
                        'ordem' => $stage->order,
                        'is_default' => $stage->is_default,
                        'oportunidade_id' => $stage->oportunidade_id,
                        'email_enabled' => $settings ? $settings->email_enabled : false,
                        'email_subject' => $settings ? $settings->email_subject : null,
                        'email_body' => $settings ? $settings->email_body : null,
                    ];
                });
                
            return response()->json($result);
            
        } catch (\Exception $e) {
            Log::error('Error on KanbanBoardController@getStatesByOportunidade: ' . $e->getMessage(), [
                'oportunidadeSlugOrId' => $oportunidadeSlugOrId,
                'user' => $request->user()->id ?? 'guest',
                'exception' => $e
            ]);
            
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
    
    /**
     * Atualiza as configurações de email para um estágio específico
     */
    public function updateEmailConfig(Request $request, $id)
    {
        // Log para depuração
        Log::info('updateEmailConfig chamado com ID: ' . $id, [
            'request' => $request->all(),
            'user' => $request->user()->id ?? 'guest',
            'url' => $request->fullUrl()
        ]);
        
        try {
            $validator = Validator::make($request->all(), [
                'email_enabled' => 'required|boolean',
                'email_subject' => 'nullable|string|required_if:email_enabled,true',
                'email_body' => 'nullable|string|required_if:email_enabled,true',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Buscar o estágio
            $stage = KanbanStage::findOrFail($id);
            
            // Buscar ou criar as configurações do estágio
            $settings = KanbanStageSetting::firstOrNew(['stage_id' => $id]);
            
            // Atualizar as configurações
            $settings->email_enabled = $request->email_enabled;
            $settings->email_subject = $request->email_subject;
            $settings->email_body = $request->email_body;
            $settings->save();
            
            return response()->json([
                'message' => 'Configurações de email atualizadas com sucesso',
                'data' => [
                    'id' => $stage->id,
                    'nome' => $stage->name,
                    'email_enabled' => $settings->email_enabled,
                    'email_subject' => $settings->email_subject,
                    'email_body' => $settings->email_body
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error on KanbanBoardController@updateEmailConfig: ' . $e->getMessage(), [
                'id' => $id,
                'user' => $request->user()->id ?? 'guest',
                'exception' => $e
            ]);
            
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
    
    /**
     * Gera um preview do template de email
     */
    public function previewEmailTemplate(Request $request, $id)
    {
        // Log para depuração
        Log::info('previewEmailTemplate chamado com ID: ' . $id, [
            'request' => $request->all(),
            'user' => $request->user()->id ?? 'guest',
            'url' => $request->fullUrl()
        ]);
        
        try {
            $validator = Validator::make($request->all(), [
                'email_subject' => 'required|string',
                'email_body' => 'required|string',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Buscar o estágio
            $stage = KanbanStage::findOrFail($id);
            
            // Variáveis de exemplo para substituição
            $demoData = [
                '{nome}' => 'Nome do Candidato',
                '{oportunidade}' => 'Título da Oportunidade',
                '{empresa}' => 'Nome da Empresa',
                '{link}' => 'https://cv-analyst.com/oportunidades/exemplo'
            ];
            
            // Substituir placeholders no assunto e corpo do email
            $subject = $request->email_subject;
            $body = $request->email_body;
            
            foreach ($demoData as $placeholder => $value) {
                $subject = str_replace($placeholder, $value, $subject);
                $body = str_replace($placeholder, $value, $body);
            }
            
            return response()->json([
                'preview' => [
                    'subject' => $subject,
                    'body' => $body
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error on KanbanBoardController@previewEmailTemplate: ' . $e->getMessage(), [
                'id' => $id,
                'user' => $request->user()->id ?? 'guest',
                'exception' => $e
            ]);
            
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
    
    /**
     * Atualiza um estado específico de uma oportunidade
     */
    public function updateStateForOportunidade(Request $request, $oportunidade_id, $state_id)
    {
        try {
            $company = $request->user();
            
            // Verificar se a oportunidade pertence à empresa
            $oportunidade = $company->oportunidades()->findOrFail($oportunidade_id);
            
            // Validar dados
            $validated = $request->validate([
                'nome' => 'sometimes|string|max:255',
                'name' => 'sometimes|string|max:255',
                'cor' => 'sometimes|string|max:7',
                'color' => 'sometimes|string|max:7',
                'send_email' => 'sometimes|boolean',
                'email_enabled' => 'sometimes|boolean',
                'email_subject' => 'nullable|string|max:255',
                'email_body' => 'nullable|string',
            ]);
            
            // Buscar o estado específico (pode ser personalizado da oportunidade ou padrão)
            $stage = KanbanStage::where('id', $state_id)
                ->where(function ($query) use ($oportunidade) {
                    $query->where('oportunidade_id', $oportunidade->id)
                          ->orWhere('is_default', true);
                })
                ->firstOrFail();
            
            // Preparar dados para atualização (suportar diferentes formatos)
            $updateData = [];
            
            if (isset($validated['name'])) {
                $updateData['name'] = $validated['name'];
            } elseif (isset($validated['nome'])) {
                $updateData['name'] = $validated['nome'];
            }
            
            // Atualizar o estado se houver dados
            if (!empty($updateData)) {
                $stage->update($updateData);
            }
            
            // Preparar configurações de email
            $emailEnabled = $validated['email_enabled'] ?? $validated['send_email'] ?? false;
            $emailSubject = $validated['email_subject'] ?? null;
            $emailBody = $validated['email_body'] ?? null;
            $color = $validated['color'] ?? $validated['cor'] ?? null;
            
            // Atualizar configurações de email se existirem
            if ($stage->settings) {
                $stage->settings->update([
                    'color' => $color,
                    'email_enabled' => $emailEnabled,
                    'email_subject' => $emailSubject,
                    'email_body' => $emailBody,
                ]);
            } else {
                // Criar configurações se não existirem
                KanbanStageSetting::create([
                    'stage_id' => $stage->id,
                    'color' => $color,
                    'email_enabled' => $emailEnabled,
                    'email_subject' => $emailSubject,
                    'email_body' => $emailBody,
                ]);
            }
            
            return response()->json([
                'message' => 'Estado atualizado com sucesso',
                'data' => $stage->load('settings')
            ], 200);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Estado ou oportunidade não encontrada'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error on KanbanBoardController@updateStateForOportunidade: ' . $e->getMessage(), [
                'oportunidade_id' => $oportunidade_id,
                'state_id' => $state_id,
                'user' => $request->user()->id ?? 'guest',
                'exception' => $e
            ]);
            
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
}
