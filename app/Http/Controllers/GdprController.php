<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Candidato;
use App\Models\Candidatura;

class GdprController extends Controller
{
    /**
     * Solicitar exportação de dados pessoais (Direito de Portabilidade)
     */
    public function exportPersonalData(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        try {
            $candidato = Candidato::where('email', $request->email)->first();
            
            if (!$candidato) {
                return response()->json([
                    'message' => 'Nenhum dado encontrado para este email.'
                ], 404);
            }

            // Dados do candidato
            $personalData = [
                'candidato' => [
                    'id' => $candidato->id,
                    'nome' => $candidato->nome,
                    'apelido' => $candidato->apelido,
                    'email' => $candidato->email,
                    'telefone' => $candidato->telefone,
                    'linkedin_url' => $candidato->linkedin_url,
                    'skills' => $candidato->skills,
                    'experiencia_profissional' => $candidato->experiencia_profissional,
                    'formacao_academica' => $candidato->formacao_academica,
                    'created_at' => $candidato->created_at,
                    'updated_at' => $candidato->updated_at,
                ],
                'candidaturas' => []
            ];

            // Candidaturas relacionadas
            $candidaturas = Candidatura::where('candidato_id', $candidato->id)
                ->orWhere('email', $request->email)
                ->get();

            foreach ($candidaturas as $candidatura) {
                $personalData['candidaturas'][] = [
                    'id' => $candidatura->id,
                    'oportunidade_id' => $candidatura->oportunidade_id,
                    'nome' => $candidatura->nome,
                    'apelido' => $candidatura->apelido,
                    'email' => $candidatura->email,
                    'telefone' => $candidatura->telefone,
                    'linkedin_url' => $candidatura->linkedin_url,
                    'skills' => $candidatura->skills,
                    'created_at' => $candidatura->created_at,
                ];
            }

            // Log da solicitação de exportação
            Log::info('Exportação de dados GDPR solicitada', [
                'email' => $candidato->email,
                'candidato_id' => $candidato->id,
                'timestamp' => now()
            ]);

            return response()->json([
                'message' => 'Dados pessoais exportados com sucesso',
                'data' => $personalData,
                'export_date' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Erro na exportação GDPR', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Erro interno ao processar solicitação'
            ], 500);
        }
    }

    /**
     * Solicitar remoção de dados pessoais (Direito ao Esquecimento)
     */
    public function deletePersonalData(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'confirmation' => 'required|in:CONFIRMO_EXCLUSAO'
        ]);

        try {
            $candidato = Candidato::where('email', $request->email)->first();
            
            if (!$candidato) {
                return response()->json([
                    'message' => 'Nenhum dado encontrado para este email.'
                ], 404);
            }

            // Log da solicitação antes da exclusão
            Log::warning('Solicitação de exclusão GDPR', [
                'email' => $candidato->email,
                'candidato_id' => $candidato->id,
                'timestamp' => now()
            ]);

            // Remover dados permanentemente usando o método do trait
            $candidato->permanentlyErasePersonalData();

            // Marcar candidaturas relacionadas para anonimização
            Candidatura::where('candidato_id', $candidato->id)
                ->orWhere('email', $request->email)
                ->update([
                    'nome' => '[REMOVIDO]',
                    'apelido' => '[REMOVIDO]',
                    'email' => '[REMOVIDO]',
                    'telefone' => null,
                    'cv_path' => null,
                    'linkedin_url' => null,
                    'skills' => null,
                    'skills_extraidas' => null,
                ]);

            // Soft delete do candidato
            $candidato->delete();

            Log::info('Dados pessoais removidos com sucesso - GDPR', [
                'candidato_id' => $candidato->id,
                'timestamp' => now()
            ]);

            return response()->json([
                'message' => 'Dados pessoais removidos com sucesso conforme GDPR',
                'deletion_date' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Erro na remoção GDPR', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Erro interno ao processar solicitação'
            ], 500);
        }
    }

    /**
     * Retornar política de privacidade da aplicação
     */
    public function privacyPolicy()
    {
        return response()->json([
            'privacy_policy' => [
                'data_collection' => [
                    'personal_data' => [
                        'nome', 'apelido', 'email', 'telefone', 'linkedin_url'
                    ],
                    'professional_data' => [
                        'skills', 'experiencia_profissional', 'formacao_academica', 'cv'
                    ],
                    'technical_data' => [
                        'ip_address', 'user_agent', 'cookies'
                    ]
                ],
                'data_usage' => [
                    'Processamento de candidaturas',
                    'Matching com oportunidades de emprego',
                    'Comunicação sobre vagas relevantes',
                    'Análise de desempenho da plataforma'
                ],
                'data_protection' => [
                    'encryption' => 'Todos os dados pessoais são criptografados',
                    'access_control' => 'Acesso restrito apenas a pessoal autorizado',
                    'retention' => 'Dados mantidos por no máximo 7 anos ou até solicitação de remoção'
                ],
                'user_rights' => [
                    'access' => 'Direito de acesso aos seus dados',
                    'portability' => 'Direito de exportar seus dados',
                    'rectification' => 'Direito de corrigir dados incorretos',
                    'erasure' => 'Direito ao esquecimento (remoção de dados)',
                    'restriction' => 'Direito de restringir processamento',
                    'objection' => 'Direito de objeção ao processamento'
                ],
                'contact' => [
                    'dpo_email' => config('mail.admin_email', 'privacy@cvanalyst.com'),
                    'company' => config('app.name'),
                ]
            ],
            'last_updated' => '2025-07-05'
        ]);
    }

    /**
     * Verificar status de consentimento GDPR
     */
    public function consentStatus(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $candidato = Candidato::where('email', $request->email)->first();
        
        if (!$candidato) {
            return response()->json([
                'message' => 'Candidato não encontrado'
            ], 404);
        }

        return response()->json([
            'consent_status' => [
                'data_processing' => true, // Se o candidato existe, consentiu
                'marketing' => $candidato->is_searchable ?? false,
                'profile_public' => $candidato->is_searchable ?? false,
                'consent_date' => $candidato->created_at,
                'last_updated' => $candidato->updated_at
            ]
        ]);
    }
}
