<?php

namespace App\Http\Controllers;

use App\Models\Oportunidade;
use App\Services\ResendService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class CampanhaMarketingController extends Controller
{
    protected $resendService;

    public function __construct(ResendService $resendService)
    {
        $this->resendService = $resendService;
    }

    /**
     * Envia uma campanha de email para todos os candidatos de uma oportunidade
     * 
     * @param Request $request
     * @param int $oportunidadeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function enviarCampanha(Request $request, $oportunidadeId)
    {
        // Validar parâmetros
        $validated = $request->validate([
            'assunto' => 'required|string|max:255',
            'conteudo_html' => 'required|string',
            'remetente' => 'required|email'
        ]);

        // Verificar se a oportunidade existe
        $oportunidade = Oportunidade::findOrFail($oportunidadeId);

        try {
            // Substituir variáveis no template
            $conteudoHtml = $this->substituirVariaveisTemplate(
                $validated['conteudo_html'], 
                [
                    'titulo_oportunidade' => $oportunidade->titulo,
                    'descricao_oportunidade' => $oportunidade->descricao
                ]
            );

            // Enviar campanha
            $resultado = $this->resendService->enviarCampanhaParaOportunidade(
                $oportunidadeId,
                $validated['assunto'],
                $conteudoHtml,
                $validated['remetente']
            );

            if ($resultado['sucesso']) {
                return response()->json([
                    'message' => 'Campanha enviada com sucesso!',
                    'detalhes' => $resultado
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Falha ao enviar campanha.',
                    'detalhes' => $resultado
                ], 400);
            }
        } catch (Exception $e) {
            Log::error('Erro ao enviar campanha: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao enviar campanha.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Substitui variáveis no template HTML
     * 
     * @param string $template
     * @param array $variaveis
     * @return string
     */
    private function substituirVariaveisTemplate($template, $variaveis)
    {
        foreach ($variaveis as $chave => $valor) {
            $template = str_replace('{{' . $chave . '}}', $valor, $template);
        }
        return $template;
    }
}
