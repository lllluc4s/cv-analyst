<?php

namespace App\Http\Controllers;

use App\Models\FeedbackRecrutamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FeedbackRecrutamentoController extends Controller
{
    /**
     * Mostrar formulário de feedback (rota pública)
     */
    public function mostrarFormulario($token)
    {
        try {
            $feedback = FeedbackRecrutamento::where('token', $token)
                ->with(['colaborador', 'candidatura', 'oportunidade.company'])
                ->first();

            if (!$feedback) {
                return response()->json([
                    'message' => 'Token de feedback inválido ou expirado'
                ], 404);
            }

            // Verificar se já foi respondido
            if ($feedback->isRespondido()) {
                return response()->json([
                    'message' => 'Este feedback já foi respondido',
                    'feedback' => $feedback
                ], 200);
            }

            return response()->json([
                'feedback' => $feedback,
                'colaborador' => $feedback->colaborador,
                'oportunidade' => $feedback->oportunidade,
                'company' => $feedback->oportunidade->company
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de feedback: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Submeter feedback (rota pública)
     */
    public function submeterFeedback(Request $request, $token)
    {
        try {
            $feedback = FeedbackRecrutamento::where('token', $token)->first();

            if (!$feedback) {
                return response()->json([
                    'message' => 'Token de feedback inválido ou expirado'
                ], 404);
            }

            if ($feedback->isRespondido()) {
                return response()->json([
                    'message' => 'Este feedback já foi respondido'
                ], 400);
            }

            $validated = $request->validate([
                'avaliacao_processo' => 'required|integer|min:1|max:5',
                'gostou_mais' => 'nullable|string|max:1000',
                'poderia_melhorar' => 'nullable|string|max:1000'
            ]);

            $feedback->update([
                'avaliacao_processo' => $validated['avaliacao_processo'],
                'gostou_mais' => $validated['gostou_mais'],
                'poderia_melhorar' => $validated['poderia_melhorar'],
                'respondido_em' => now()
            ]);

            Log::info('Feedback de recrutamento submetido', [
                'feedback_id' => $feedback->id,
                'colaborador_id' => $feedback->colaborador_id,
                'avaliacao' => $validated['avaliacao_processo']
            ]);

            return response()->json([
                'message' => 'Feedback enviado com sucesso! Obrigado pela sua contribuição.',
                'feedback' => $feedback->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao submeter feedback: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Listar feedbacks para uma oportunidade (apenas para empresas)
     */
    public function listarFeedbacks(Request $request, $oportunidadeId)
    {
        try {
            $company = $request->user();
            
            // Verificar se a oportunidade pertence à empresa
            $oportunidade = $company->oportunidades()->findOrFail($oportunidadeId);

            $feedbacks = FeedbackRecrutamento::where('oportunidade_id', $oportunidadeId)
                ->whereNotNull('respondido_em') // Apenas feedbacks respondidos
                ->with(['colaborador'])
                ->orderBy('respondido_em', 'desc')
                ->get();

            // Estatísticas básicas
            $stats = [
                'total_feedbacks' => $feedbacks->count(),
                'avaliacao_media' => $feedbacks->avg('avaliacao_processo'),
                'distribuicao_avaliacoes' => [
                    '5' => $feedbacks->where('avaliacao_processo', 5)->count(),
                    '4' => $feedbacks->where('avaliacao_processo', 4)->count(),
                    '3' => $feedbacks->where('avaliacao_processo', 3)->count(),
                    '2' => $feedbacks->where('avaliacao_processo', 2)->count(),
                    '1' => $feedbacks->where('avaliacao_processo', 1)->count(),
                ]
            ];

            return response()->json([
                'oportunidade' => $oportunidade,
                'feedbacks' => $feedbacks,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao listar feedbacks: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
}
