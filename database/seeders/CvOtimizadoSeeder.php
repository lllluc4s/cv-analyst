<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CvOtimizado;
use App\Models\Candidato;

class CvOtimizadoSeeder extends Seeder
{
    public function run(): void
    {
        // Garantir 4 candidatos base para amarrar os CVs
        $candidatos = Candidato::factory()->count(4)->create([
            'is_searchable' => true,
        ]);

        // Quatro currículos fictícios, mais descritivos
        $dados = [
            [
                'titulo' => 'Desenvolvedor Backend PHP/Laravel Sênior',
                'resumo_pessoal' => 'Engenheiro de software com 8+ anos, foco em APIs REST escaláveis, mensageria e testes automatizados.',
                'experiencias' => [
                    [
                        'empresa' => 'Fintech NovaEra',
                        'cargo' => 'Senior Backend Engineer',
                        'inicio' => '2021-02',
                        'fim' => null,
                        'descricao' => 'Liderança técnica, microsserviços Laravel, filas com Redis, eventos, testes (Pest/PHPUnit).',
                    ],
                    [
                        'empresa' => 'LogiTech Transportes',
                        'cargo' => 'Desenvolvedor PHP',
                        'inicio' => '2018-01',
                        'fim' => '2021-01',
                        'descricao' => 'Integrações com ERPs (REST/SOAP), otimização de queries MySQL, caching e monitoramento.',
                    ],
                ],
                'skills' => ['Laravel','PHP 8','MySQL','Redis','Docker','CI/CD','TDD','REST','RabbitMQ','OpenAPI'],
                'formacao' => [[
                    'curso' => 'Engenharia de Computação',
                    'instituicao' => 'Universidade Federal Tech',
                    'conclusao' => '2017',
                ]],
                'dados_pessoais' => [
                    'linkedin' => 'https://www.linkedin.com/in/backend-senior',
                    'email' => 'backend.senior@example.com',
                    'telefone' => '+55 11 99999-1111',
                    'localizacao' => 'São Paulo, SP',
                ],
                'template_escolhido' => 'padrao-1',
                'cv_original_texto' => 'Responsável por arquitetar e evoluir APIs de alto desempenho, com atenção a observabilidade e segurança.',
                'cv_original_path' => null,
                'otimizado_por_ia' => true,
            ],
            [
                'titulo' => 'Desenvolvedora Frontend Vue.js',
                'resumo_pessoal' => '4+ anos em SPA com Vue 3, Vite e Tailwind. Foco em DX, acessibilidade e design system.',
                'experiencias' => [
                    [
                        'empresa' => 'HealthCare Plus',
                        'cargo' => 'Frontend Developer',
                        'inicio' => '2022-04',
                        'fim' => null,
                        'descricao' => 'Criação de componentes reusáveis, Storybook, testes com Vitest, integrações com REST.',
                    ],
                    [
                        'empresa' => 'EduLabs',
                        'cargo' => 'Jr Frontend Dev',
                        'inicio' => '2020-01',
                        'fim' => '2022-03',
                        'descricao' => 'Migração de Vue 2 para Vue 3, performance e melhorias em UX.',
                    ],
                ],
                'skills' => ['Vue 3','Vite','Tailwind','TypeScript','Pinia','Axios','A11y','Jest/Vitest'],
                'formacao' => [[
                    'curso' => 'Sistemas de Informação',
                    'instituicao' => 'Instituto TechLab',
                    'conclusao' => '2019',
                ]],
                'dados_pessoais' => [
                    'linkedin' => 'https://www.linkedin.com/in/frontend-vue',
                    'email' => 'frontend.vue@example.com',
                    'telefone' => '+55 21 98888-2222',
                    'localizacao' => 'Rio de Janeiro, RJ',
                ],
                'template_escolhido' => 'moderno-azul',
                'cv_original_texto' => 'Entrega de interfaces acessíveis, pixel perfect e responsivas, com SSR quando necessário.',
                'cv_original_path' => null,
                'otimizado_por_ia' => false,
            ],
            [
                'titulo' => 'Cientista de Dados Pleno',
                'resumo_pessoal' => 'Modelagem preditiva, experimentos e dashboards. Experiência com dados em produção.',
                'experiencias' => [
                    [
                        'empresa' => 'Retail Insights',
                        'cargo' => 'Data Scientist',
                        'inicio' => '2021-08',
                        'fim' => null,
                        'descricao' => 'Modelos de recomendação, AB testing, feature store e ML pipelines.',
                    ],
                    [
                        'empresa' => 'Dados&Ação',
                        'cargo' => 'Data Analyst',
                        'inicio' => '2019-02',
                        'fim' => '2021-07',
                        'descricao' => 'Dashboards e análises exploratórias para áreas de negócio.',
                    ],
                ],
                'skills' => ['Python','Pandas','Scikit-Learn','SQL','Airflow','dbt','GCP','Streamlit'],
                'formacao' => [[
                    'curso' => 'Estatística',
                    'instituicao' => 'Universidade Alfa',
                    'conclusao' => '2018',
                ]],
                'dados_pessoais' => [
                    'linkedin' => 'https://www.linkedin.com/in/data-pleno',
                    'email' => 'data.pleno@example.com',
                    'telefone' => '+55 31 97777-3333',
                    'localizacao' => 'Belo Horizonte, MG',
                ],
                'template_escolhido' => 'clean',
                'cv_original_texto' => 'Foco em métricas de negócio, interpretabilidade e manutenção de modelos em produção.',
                'cv_original_path' => null,
                'otimizado_por_ia' => true,
            ],
            [
                'titulo' => 'DevOps | Platform Engineer',
                'resumo_pessoal' => 'Automação, observabilidade e confiabilidade. Redução de MTTR e custo de infraestrutura.',
                'experiencias' => [
                    [
                        'empresa' => 'ScaleOps',
                        'cargo' => 'Platform Engineer',
                        'inicio' => '2020-05',
                        'fim' => null,
                        'descricao' => 'IaC com Terraform, Kubernetes, pipelines GitHub Actions e monitoramento com Prometheus/Grafana.',
                    ],
                ],
                'skills' => ['Terraform','Kubernetes','Docker','GitHub Actions','Prometheus','Grafana','AWS','Linux'],
                'formacao' => [[
                    'curso' => 'Engenharia de Software',
                    'instituicao' => 'Universidade Sigma',
                    'conclusao' => '2016',
                ]],
                'dados_pessoais' => [
                    'linkedin' => 'https://www.linkedin.com/in/platform-eng',
                    'email' => 'platform.eng@example.com',
                    'telefone' => '+55 41 96666-4444',
                    'localizacao' => 'Curitiba, PR',
                ],
                'template_escolhido' => 'dark',
                'cv_original_texto' => 'Construção de plataformas internas com foco em produtividade e segurança by default.',
                'cv_original_path' => null,
                'otimizado_por_ia' => false,
            ],
        ];

        foreach ($candidatos as $i => $cand) {
            $payload = $dados[$i];
            CvOtimizado::create(array_merge($payload, [
                'candidato_id' => $cand->id,
            ]));
        }
    }
}
