<?php

namespace App\Services;

use App\Models\CvOtimizado;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfGeneratorService
{
    /**
     * Gera PDF do CV otimizado
     */
    public function gerarPDF(CvOtimizado $cv, string $template = 'moderno'): array
    {
        try {
            // Preparar dados para o template
            $dados = [
                'cv' => $cv,
                'dados_pessoais' => $cv->dados_pessoais,
                'resumo_pessoal' => $cv->resumo_pessoal,
                'experiencias' => $cv->experiencias,
                'skills' => $cv->skills,
                'formacao' => $cv->formacao,
                'template' => $template
            ];

            // Determinar qual template usar
            $viewTemplate = $this->getTemplatePath($template);
            
            // Gerar PDF
            $pdf = PDF::loadView($viewTemplate, $dados);
            $pdf->setPaper('A4', 'portrait');
            
            // Salvar PDF
            $nomeArquivo = 'cv_' . $cv->candidato->slug . '_' . time() . '.pdf';
            $caminhoCompleto = 'cvs/gerados/' . $nomeArquivo;
            
            $conteudoPdf = $pdf->output();
            Storage::disk('public')->put($caminhoCompleto, $conteudoPdf);
            
            return [
                'success' => true,
                'path' => $caminhoCompleto,
                'url' => asset('storage/' . $caminhoCompleto),
                'filename' => $nomeArquivo
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao gerar PDF: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erro ao gerar PDF: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Retorna o caminho do template baseado na escolha
     */
    private function getTemplatePath(string $template): string
    {
        $templates = [
            'classico' => 'cvs.templates.classico',
            'criativo' => 'cvs.templates.criativo'
        ];

        return $templates[$template] ?? $templates['classico'];
    }

    /**
     * Lista templates disponíveis
     */
    public function getTemplatesDisponiveis(): array
    {
        return [
            [
                'id' => 'classico',
                'nome' => 'Clássico',
                'descricao' => 'Layout tradicional e profissional',
                'preview' => '/images/templates/classico-preview.svg'
            ],
            [
                'id' => 'criativo',
                'nome' => 'Criativo',
                'descricao' => 'Visual inovador e diferenciado',
                'preview' => '/images/templates/criativo-preview.svg'
            ]
        ];
    }
}
