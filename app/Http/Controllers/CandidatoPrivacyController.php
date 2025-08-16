<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\Candidato;
use App\Models\Candidatura;

class CandidatoPrivacyController extends Controller
{
    /**
     * Obter configurações de privacidade do candidato
     */
    public function getPrivacySettings()
    {
        $candidato = Auth::user();
        
        return response()->json([
            'is_searchable' => $candidato->is_searchable,
            'consentimento_emails' => $candidato->consentimento_emails,
            'email' => $candidato->email,
            'created_at' => $candidato->created_at,
        ]);
    }

    /**
     * Atualizar configurações de privacidade e consentimento
     */
    public function updateSearchability(Request $request)
    {
        Log::info('Update privacy settings request', [
            'request_data' => $request->all(),
            'user_id' => Auth::id()
        ]);
        
        $request->validate([
            'is_searchable' => 'required|boolean',
            'consentimento_emails' => 'sometimes|boolean',
        ]);

        /** @var Candidato $candidato */
        $candidato = Auth::user();
        
        Log::info('Current user privacy settings', [
            'user_id' => $candidato->id,
            'current_is_searchable' => $candidato->is_searchable,
            'requested_is_searchable' => $request->is_searchable,
            'current_consentimento_emails' => $candidato->consentimento_emails,
            'requested_consentimento_emails' => $request->consentimento_emails
        ]);
        
        // Atualizar as configurações de privacidade
        $candidato->is_searchable = $request->is_searchable;
        
        // Atualizar consentimento de emails se foi fornecido
        if ($request->has('consentimento_emails')) {
            $candidato->consentimento_emails = $request->consentimento_emails;
            $candidato->consentimento_emails_data = now();
            $candidato->pode_ser_contactado = $request->consentimento_emails;
        }
        
        $saved = $candidato->save();
        
        Log::info('Privacy settings update result', [
            'user_id' => $candidato->id,
            'save_result' => $saved,
            'new_is_searchable' => $candidato->is_searchable,
            'new_consentimento_emails' => $candidato->consentimento_emails
        ]);

        return response()->json([
            'message' => 'Configurações de privacidade atualizadas com sucesso',
            'is_searchable' => $candidato->fresh()->is_searchable,
            'consentimento_emails' => $candidato->fresh()->consentimento_emails,
        ]);
    }

    /**
     * Solicitar exclusão permanente da conta
     */
    public function requestAccountDeletion(Request $request)
    {
        Log::info('Delete account request received', [
            'request_data' => $request->all(),
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : 'null'
        ]);
        
        $validated = $request->validate([
            'password' => 'required|string',
            'confirmation' => 'required|string|in:DELETE_MY_ACCOUNT',
        ]);
        
        Log::info('Validation passed', ['validated' => $validated]);

        $candidato = Auth::user();
        
        if (!$candidato) {
            Log::error('No authenticated user found');
            return response()->json([
                'message' => 'Usuário não autenticado'
            ], 401);
        }
        
        Log::info('User found for deletion', [
            'user_id' => $candidato->id,
            'user_email' => $candidato->email,
            'user_class' => get_class($candidato)
        ]);

        // Verificar senha
        if (!Hash::check($request->password, $candidato->password)) {
            Log::warning('Password verification failed for account deletion', [
                'user_id' => $candidato->id
            ]);
            return response()->json([
                'message' => 'Senha incorreta'
            ], 422);
        }

        Log::info('Password verified, proceeding with account deletion', [
            'user_id' => $candidato->id
        ]);

        try {
            // Excluir conta permanentemente
            $candidatoModel = Candidato::find($candidato->id);
            if ($candidatoModel) {
                $candidatoModel->deleteAccountPermanently();
            }
            
            Log::info('Account successfully deleted', [
                'user_id' => $candidato->id
            ]);
            
            // Para APIs stateless, não precisamos fazer logout
            // O frontend deve remover o token do storage

            return response()->json([
                'message' => 'Conta excluída permanentemente com sucesso'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting account', [
                'user_id' => $candidato->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Erro interno ao deletar conta'
            ], 500);
        }
    }

    /**
     * Exportar dados do candidato (GDPR compliance)
     */
    public function exportData()
    {
        /** @var Candidato $candidato */
        $candidato = Auth::user();
        
        // Buscar candidaturas do usuário
        $candidaturas = Candidatura::where('candidato_id', $candidato->id)
            ->with('oportunidade')
            ->get();
        
        $data = [
            'dados_pessoais' => [
                'id' => $candidato->id,
                'nome' => $candidato->nome,
                'apelido' => $candidato->apelido,
                'email' => $candidato->email,
                'telefone' => $candidato->telefone,
                'linkedin_url' => $candidato->linkedin_url,
                'cv_path' => $candidato->cv_path,
            ],
            'dados_profissionais' => [
                'skills' => $candidato->skills,
                'experiencia_profissional' => $candidato->experiencia_profissional,
                'formacao_academica' => $candidato->formacao_academica,
            ],
            'configuracoes_privacidade' => [
                'is_searchable' => $candidato->is_searchable,
                'consentimento_emails' => $candidato->consentimento_emails,
                'consentimento_emails_data' => $candidato->consentimento_emails_data,
                'pode_ser_contactado' => $candidato->pode_ser_contactado,
            ],
            'candidaturas' => $candidaturas->map(function ($candidatura) {
                return [
                    'id' => $candidatura->id,
                    'vaga_titulo' => $candidatura->oportunidade?->titulo ?? 'Vaga não encontrada',
                    'vaga_empresa' => $candidatura->oportunidade?->empresa ?? 'N/A',
                    'status' => $candidatura->status ?? 'pendente',
                    'data_candidatura' => $candidatura->created_at,
                    'pontuacao_skills' => $candidatura->pontuacao_skills,
                    'score' => $candidatura->score,
                    'ranking' => $candidatura->ranking,
                ];
            })->toArray(),
            'metadados' => [
                'criado_em' => $candidato->created_at,
                'atualizado_em' => $candidato->updated_at,
                'email_verificado_em' => $candidato->email_verified_at,
                'total_candidaturas' => $candidaturas->count(),
                'exportado_em' => now(),
                'formato' => 'JSON',
                'versao_exportacao' => '1.0',
            ],
        ];

        return response()->json($data, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="meus_dados_' . now()->format('Y-m-d') . '.json"'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
