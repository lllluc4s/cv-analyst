<?php

namespace App\Http\Controllers;

use App\Models\CvOtimizado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\CvOptimizationService;
use Illuminate\Support\Facades\Storage;

class CvOptimizerController extends Controller
{
    protected $cvOptimizationService;

    public function __construct(CvOptimizationService $cvOptimizationService)
    {
        $this->cvOptimizationService = $cvOptimizationService;
    }

    /**
     * Upload e extração inicial do CV
     */
    public function uploadCV(Request $request)
    {
        try {
            $request->validate([
                'cv_file' => 'required|file|mimes:pdf,doc,docx|max:10240' // 10MB
            ]);

            $candidato = $request->user();

            $resultado = $this->cvOptimizationService->extrairConteudoCV(
                $request->file('cv_file'),
                $candidato
            );

            if ($resultado['success']) {
                return response()->json([
                    'message' => 'CV extraído com sucesso',
                    'cv_id' => $resultado['cv_id'],
                    'dados' => $resultado['dados']
                ]);
            } else {
                return response()->json([
                    'message' => $resultado['error']
                ], 400);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Arquivo inválido',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro no upload do CV: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Otimizar CV com IA
     */
    public function otimizarCV(Request $request, $cvId)
    {
        try {
            $request->validate([
                'setor' => 'nullable|string|max:100'
            ]);

            $candidato = $request->user();

            // Verificar se o CV pertence ao candidato logado
            $cv = CvOtimizado::where('id', $cvId)
                             ->where('candidato_id', $candidato->id)
                             ->firstOrFail();

            $resultado = $this->cvOptimizationService->otimizarComIA(
                $cvId,
                $request->input('setor')
            );

            if ($resultado['success']) {
                return response()->json([
                    'message' => 'CV otimizado com sucesso',
                    'dados_otimizados' => $resultado['dados_otimizados']
                ]);
            } else {
                return response()->json([
                    'message' => $resultado['error']
                ], 400);
            }

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'CV não encontrado'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Erro ao otimizar CV: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Atualizar dados do CV manualmente
     */
    public function atualizarCV(Request $request, $cvId)
    {
        try {
            $candidato = $request->user();

            $cv = CvOtimizado::where('id', $cvId)
                             ->where('candidato_id', $candidato->id)
                             ->firstOrFail();

            $request->validate([
                'dados_pessoais' => 'sometimes|array',
                'resumo_pessoal' => 'sometimes|string|max:1000',
                'experiencias' => 'sometimes|array',
                'skills' => 'sometimes|array',
                'formacao' => 'sometimes|array',
                'template_escolhido' => 'sometimes|string|in:classico,criativo,moderno'
            ]);

            // Sanitização defensiva caso algum campo venha como string
            $payload = $request->only([
                'dados_pessoais',
                'resumo_pessoal',
                'experiencias',
                'skills',
                'formacao',
                'template_escolhido'
            ]);

            foreach (['experiencias','skills','formacao'] as $k) {
                if (isset($payload[$k]) && !is_array($payload[$k])) {
                    // Tentar decodificar se for JSON, caso contrário descartar para evitar 500
                    if (is_string($payload[$k])) {
                        $decoded = json_decode($payload[$k], true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $payload[$k] = $decoded;
                        } else {
                            unset($payload[$k]);
                        }
                    } else {
                        unset($payload[$k]);
                    }
                }
            }

            $cv->update($payload);

            return response()->json([
                'message' => 'CV atualizado com sucesso',
                'cv' => $cv
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'CV não encontrado'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar CV: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Obter CV do candidato
     */
    public function obterCV(Request $request, $cvId)
    {
        try {
            $candidato = $request->user();

            $cv = CvOtimizado::where('id', $cvId)
                             ->where('candidato_id', $candidato->id)
                             ->firstOrFail();

            return response()->json([
                'cv' => $cv
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'CV não encontrado'
            ], 404);
        }
    }

    /**
     * Listar CVs do candidato
     */
    public function listarCVs(Request $request)
    {
        try {
            $candidato = $request->user();

            $cvs = CvOtimizado::where('candidato_id', $candidato->id)
                              ->orderBy('created_at', 'desc')
                              ->get();

            return response()->json([
                'cvs' => $cvs
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao listar CVs: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Excluir CV
     */
    public function excluirCV(Request $request, $cvId)
    {
        try {
            $candidato = $request->user();

            $cv = CvOtimizado::where('id', $cvId)
                             ->where('candidato_id', $candidato->id)
                             ->firstOrFail();

            // Remover arquivo do storage se existir
            if ($cv->cv_original_path && Storage::disk('public')->exists($cv->cv_original_path)) {
                Storage::disk('public')->delete($cv->cv_original_path);
            }

            $cv->delete();

            return response()->json([
                'message' => 'CV excluído com sucesso'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'CV não encontrado'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir CV: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Gerar PDF do CV otimizado
     */
    public function gerarPDF(Request $request, $cvId)
    {
        try {
            $request->validate([
                'template' => 'sometimes|string|in:classico,criativo'
            ]);

            $candidato = $request->user();

            $cv = CvOtimizado::where('id', $cvId)
                             ->where('candidato_id', $candidato->id)
                             ->firstOrFail();

            $template = $request->input('template', $cv->template_escolhido);

            $pdfService = new \App\Services\PdfGeneratorService();
            $resultado = $pdfService->gerarPDF($cv, $template);

            if ($resultado['success']) {
                return response()->json([
                    'message' => 'PDF gerado com sucesso',
                    'pdf_url' => $resultado['url'],
                    'filename' => $resultado['filename']
                ]);
            } else {
                return response()->json([
                    'message' => $resultado['error']
                ], 400);
            }

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'CV não encontrado'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar PDF: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Listar templates disponíveis
     */
    public function listarTemplates()
    {
        $pdfService = new \App\Services\PdfGeneratorService();
        return response()->json([
            'templates' => $pdfService->getTemplatesDisponiveis()
        ]);
    }

    /**
     * Status da IA (OpenAI) — indica se chave está configurada e se usaremos IA ou fallback
     */
    public function statusIA(Request $request)
    {
        try {
            $apiKey = config('openai.api_key') ?: env('OPENAI_API_KEY');
            if (!$apiKey) {
                return response()->json([
                    'ia_disponivel' => false,
                    'modo' => 'fallback',
                    'reason' => 'OPENAI_API_KEY não configurada'
                ]);
            }

            // Opcional: poderíamos tentar um ping real aqui, mas evitamos chamadas externas frequentes.
            return response()->json([
                'ia_disponivel' => true,
                'modo' => 'ia'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao verificar status da IA: ' . $e->getMessage());
            return response()->json([
                'ia_disponivel' => false,
                'modo' => 'fallback',
                'reason' => 'erro_interno'
            ], 200);
        }
    }
}
