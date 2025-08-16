<?php

namespace App\Console\Commands;

use App\Models\Candidato;
use App\Models\CvOtimizado;
use App\Services\PdfGeneratorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'cv:fake-pdfs')]
class GenerateFakeCvs extends Command
{
    protected $signature = 'cv:fake-pdfs {--template=classico : Template para usar (classico|moderno|criativo)}';
    protected $description = 'Gera 4 PDFs de CV fictícios sem persistir no banco.';

    public function handle(PdfGeneratorService $pdfService): int
    {
        $template = (string) $this->option('template');

        // Garantir diretório público
        Storage::disk('public')->makeDirectory('cvs/gerados');

        $fixtures = $this->fixtures();
        $gerados = [];

        foreach ($fixtures as $fx) {
            $candidato = new Candidato(['slug' => $fx['slug']]);

            $cv = new CvOtimizado([
                'candidato_id' => null,
                'titulo' => $fx['titulo'],
                'resumo_pessoal' => $fx['resumo_pessoal'],
                'experiencias' => $fx['experiencias'],
                'skills' => $fx['skills'],
                'formacao' => $fx['formacao'],
                'dados_pessoais' => $fx['dados_pessoais'],
                'template_escolhido' => $template,
                'cv_original_texto' => $fx['cv_original_texto'] ?? null,
                'cv_original_path' => null,
                'otimizado_por_ia' => true,
            ]);

            // Relacionar candidato em memória
            $cv->setRelation('candidato', $candidato);

            $res = $pdfService->gerarPDF($cv, $template);
            $gerados[] = $res;

            if (($res['success'] ?? false) && isset($res['path'])) {
                $this->info('PDF gerado: ' . $res['path']);
            } else {
                $this->error('Falha ao gerar PDF: ' . ($res['error'] ?? 'desconhecido'));
            }
        }

        $this->newLine();
        $this->line('Arquivos gerados (acesse via public/storage/...):');
        foreach ($gerados as $r) {
            if (($r['success'] ?? false)) {
                $this->line('- ' . ($r['url'] ?? ('/storage/' . $r['path'])));
            }
        }

        return self::SUCCESS;
    }

    /**
     * Dados fictícios para 4 CVs
     */
    private function fixtures(): array
    {
        return [
            [
                'slug' => 'joao-silva',
                'titulo' => 'Desenvolvedor Full Stack',
                'dados_pessoais' => [
                    'nome' => 'João Silva',
                    'email' => 'joao.silva@example.com',
                    'telefone' => '+55 11 99888-0001',
                    'linkedin' => 'https://www.linkedin.com/in/joaosilva'
                ],
                'resumo_pessoal' => 'Desenvolvedor full stack com 7 anos de experiência em JavaScript/TypeScript, Node.js e React. Forte domínio de SQL, Docker, Git e AWS.',
                'experiencias' => [
                    [
                        'cargo' => 'Senior Full Stack Developer',
                        'empresa' => 'Tech Solutions',
                        'periodo' => '2021 — Atual',
                        'descricao' => 'Arquitetura de APIs com Node.js e React, CI/CD e conteinerização com Docker.'
                    ],
                    [
                        'cargo' => 'Backend Developer',
                        'empresa' => 'FinServe',
                        'periodo' => '2018 — 2021',
                        'descricao' => 'Modelagem de dados (SQL), integrações e pipelines de deploy.'
                    ]
                ],
                'skills' => [
                    [ 'categoria' => 'Linguagens', 'habilidades' => ['JavaScript', 'TypeScript', 'SQL'] ],
                    [ 'categoria' => 'Frameworks', 'habilidades' => ['Node.js', 'React'] ],
                    [ 'categoria' => 'DevOps', 'habilidades' => ['Docker', 'Git', 'AWS'] ],
                ],
                'formacao' => [
                    [ 'curso' => 'Bacharel em Ciência da Computação', 'instituicao' => 'UFSP', 'periodo' => '2014 — 2018' ]
                ],
            ],
            [
                'slug' => 'maria-oliveira',
                'titulo' => 'Analista de Dados/BI',
                'dados_pessoais' => [
                    'nome' => 'Maria Oliveira',
                    'email' => 'maria.oliveira@example.com',
                    'telefone' => '+55 21 98877-1234',
                    'linkedin' => 'https://www.linkedin.com/in/mariabi'
                ],
                'resumo_pessoal' => 'Profissional de BI com foco em visualização e modelagem. Experiência com Power BI, Tableau e Excel para análises executivas.',
                'experiencias' => [
                    [ 'cargo' => 'BI Analyst', 'empresa' => 'SaaS Corp', 'periodo' => '2020 — Atual', 'descricao' => 'Dashboards e ETL leve com Excel e SQL.' ],
                    [ 'cargo' => 'Data Visualization Specialist', 'empresa' => 'Studio XYZ', 'periodo' => '2017 — 2020', 'descricao' => 'Criação de relatórios em Power BI e Tableau.' ],
                ],
                'skills' => [
                    [ 'categoria' => 'Ferramentas BI', 'habilidades' => ['Power BI', 'Tableau', 'Excel'] ],
                    [ 'categoria' => 'Dados', 'habilidades' => ['SQL'] ],
                ],
                'formacao' => [
                    [ 'curso' => 'Estatística', 'instituicao' => 'UFRJ', 'periodo' => '2012 — 2016' ]
                ],
            ],
            [
                'slug' => 'carlos-souza',
                'titulo' => 'Data Engineer',
                'dados_pessoais' => [
                    'nome' => 'Carlos Souza',
                    'email' => 'carlos.souza@example.com',
                    'telefone' => '+55 31 97766-4567',
                    'linkedin' => 'https://www.linkedin.com/in/carlossouza'
                ],
                'resumo_pessoal' => 'Engenheiro de dados com foco em Python e SQL, pipelines em Docker/Kubernetes e cloud AWS. Forte base em Linux.',
                'experiencias' => [
                    [ 'cargo' => 'Data Engineer', 'empresa' => 'Retail Analytics', 'periodo' => '2019 — Atual', 'descricao' => 'Construção de pipelines, conteinerização e orquestração.' ],
                ],
                'skills' => [
                    [ 'categoria' => 'Linguagens', 'habilidades' => ['Python', 'SQL'] ],
                    [ 'categoria' => 'Plataformas', 'habilidades' => ['Docker', 'Kubernetes', 'AWS', 'Linux'] ],
                ],
                'formacao' => [
                    [ 'curso' => 'Engenharia de Computação', 'instituicao' => 'UFMG', 'periodo' => '2011 — 2016' ]
                ],
            ],
            [
                'slug' => 'ana-costa',
                'titulo' => 'QA Engineer',
                'dados_pessoais' => [
                    'nome' => 'Ana Costa',
                    'email' => 'ana.costa@example.com',
                    'telefone' => '+55 41 96655-9876',
                    'linkedin' => 'https://www.linkedin.com/in/anacostaqa'
                ],
                'resumo_pessoal' => 'QA com automação em JavaScript/Node.js, testes de API e pipelines CI. Experiência com Docker, Jenkins e Linux.',
                'experiencias' => [
                    [ 'cargo' => 'QA Engineer', 'empresa' => 'HealthTech', 'periodo' => '2021 — Atual', 'descricao' => 'Automação E2E (JavaScript/Node.js), testes de API e integração contínua.' ],
                    [ 'cargo' => 'QA Analyst', 'empresa' => 'Agile Factory', 'periodo' => '2018 — 2021', 'descricao' => 'Planejamento e execução de testes funcionais.' ],
                ],
                'skills' => [
                    [ 'categoria' => 'Linguagens', 'habilidades' => ['JavaScript', 'SQL'] ],
                    [ 'categoria' => 'Ferramentas', 'habilidades' => ['Node.js', 'Docker', 'Jenkins', 'Linux'] ],
                ],
                'formacao' => [
                    [ 'curso' => 'Sistemas de Informação', 'instituicao' => 'PUCPR', 'periodo' => '2013 — 2017' ]
                ],
            ],
        ];
    }
}
