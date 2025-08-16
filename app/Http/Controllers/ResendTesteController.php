<?php

namespace App\Http\Controllers;

use App\Models\Oportunidade;
use App\Services\ResendService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class ResendTesteController extends Controller
{
    protected $resendService;

    public function __construct(ResendService $resendService)
    {
        $this->resendService = $resendService;
    }

    /**
     * Executa um teste completo da integração com Resend
     * 
     * @param Request $request
     * @param int $oportunidadeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function testarIntegracao(Request $request, $oportunidadeId)
    {
        // Verificar se a oportunidade existe
        $oportunidade = Oportunidade::findOrFail($oportunidadeId);

        try {
            // Realizar simulação de fluxo completo
            $resultado = $this->resendService->simularFluxoCompleto($oportunidadeId);

            return response()->json([
                'message' => $resultado['sucesso'] ? 'Teste de integração concluído com sucesso!' : 'Teste falhou',
                'oportunidade' => [
                    'id' => $oportunidade->id,
                    'titulo' => $oportunidade->titulo
                ],
                'resultado' => $resultado
            ], $resultado['sucesso'] ? 200 : 400);
        } catch (Exception $e) {
            Log::error('Erro ao testar integração Resend: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao testar integração',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Testa apenas a criação de um grupo de audiência
     * 
     * @param Request $request
     * @param int $oportunidadeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function testarCriacaoGrupo(Request $request, $oportunidadeId)
    {
        // Verificar se a oportunidade existe
        $oportunidade = Oportunidade::findOrFail($oportunidadeId);

        try {
            $grupoId = $this->resendService->obterOuCriarGrupoAudiencia($oportunidadeId);
            
            if ($grupoId) {
                return response()->json([
                    'message' => 'Grupo de audiência criado/obtido com sucesso!',
                    'grupo_id' => $grupoId,
                    'oportunidade' => [
                        'id' => $oportunidade->id,
                        'titulo' => $oportunidade->titulo
                    ]
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Falha ao criar/obter grupo de audiência',
                ], 400);
            }
        } catch (Exception $e) {
            Log::error('Erro ao testar criação de grupo: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao testar criação de grupo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Testa apenas o envio de campanha para uma oportunidade
     * 
     * @param Request $request
     * @param int $oportunidadeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function testarEnvioCampanha(Request $request, $oportunidadeId)
    {
        // Verificar se a oportunidade existe
        $oportunidade = Oportunidade::findOrFail($oportunidadeId);

        // Validar parâmetros
        $validated = $request->validate([
            'assunto' => 'required|string|max:255',
            'conteudo_html' => 'required|string',
            'remetente' => 'required|email'
        ]);

        try {
            // Substituir variáveis no template
            $conteudoHtml = str_replace(
                ['{{titulo_oportunidade}}', '{{id_oportunidade}}'],
                [$oportunidade->titulo, $oportunidade->id],
                $validated['conteudo_html']
            );

            // Enviar campanha
            $resultado = $this->resendService->enviarCampanhaParaOportunidade(
                $oportunidadeId,
                $validated['assunto'],
                $conteudoHtml,
                $validated['remetente']
            );

            if ($resultado['sucesso'] ?? false) {
                return response()->json([
                    'message' => 'Campanha de teste enviada com sucesso!',
                    'detalhes' => $resultado
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Falha ao enviar campanha de teste.',
                    'detalhes' => $resultado
                ], 400);
            }
        } catch (Exception $e) {
            Log::error('Erro ao testar envio de campanha: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao testar envio de campanha',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
