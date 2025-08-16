<?php

namespace Tests\Feature;

use App\Models\CvOtimizado;
use App\Services\CvOptimizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CvOptimizationFallbackTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function otimizar_com_fallback_reescreve_experiencias_com_descricao_e_conquistas()
    {
        // Arrange: criar um candidato e um CV com experiência mínima e sem resumo
        $candidato = \App\Models\Candidato::factory()->create();

        $cv = CvOtimizado::create([
            'candidato_id' => $candidato->id,
            'cv_original_texto' => 'Teste',
            'cv_original_path' => 'cvs/originais/teste.pdf',
            'dados_pessoais' => [
                'nome' => 'João Silva',
                'email' => 'joao@example.com',
                'telefone' => '(11) 99999-9999',
                'linkedin' => 'https://linkedin.com/in/joaosilva'
            ],
            'resumo_pessoal' => '',
            'experiencias' => [[
                'cargo' => 'Desenvolvedor Backend',
                'empresa' => 'Empresa X',
                'periodo' => '2021 — Atual',
                'descricao' => 'Responsável por APIs',
                'conquistas' => []
            ]],
            'skills' => [[
                'categoria' => 'Linguagens',
                'habilidades' => ['PHP', 'Laravel', 'MySQL']
            ]],
            'formacao' => [[
                'curso' => 'Sistemas de Informação',
                'instituicao' => 'UF ABC',
                'periodo' => '2016 — 2020',
                'detalhes' => ''
            ]],
            'otimizado_por_ia' => false,
        ]);

        $service = new CvOptimizationService();

        // Act: forçar caminho de fallback (sem OPENAI_API_KEY)
        $resultado = $service->otimizarComIA($cv->id, 'Tecnologia');

        // Assert
        $this->assertTrue($resultado['success']);
        $this->assertIsArray($resultado['dados_otimizados']);

        $dados = $resultado['dados_otimizados'];
        $this->assertNotEmpty($dados['resumo_pessoal']);

        $this->assertIsArray($dados['experiencias']);
        $this->assertNotEmpty($dados['experiencias']);
        $exp = $dados['experiencias'][0];
        $this->assertNotEmpty($exp['descricao'], 'Descrição de experiência deve ser reescrita no fallback');
        $this->assertIsArray($exp['conquistas']);
        $this->assertNotEmpty($exp['conquistas'], 'Conquistas devem ser preenchidas no fallback');
    }
}
