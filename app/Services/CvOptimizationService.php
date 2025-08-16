<?php

namespace App\Services;

use OpenAI;
use App\Models\Candidato;
use Spatie\PdfToText\Pdf;
use App\Models\CvOtimizado;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class CvOptimizationService
{
    /**
     * Extrai conteúdo de um CV em PDF e cria versão editável
     */
    public function extrairConteudoCV(UploadedFile $arquivo, Candidato $candidato): array
    {
        try {
            // Extrair texto do PDF
            $textoExtraido = $this->extrairTextoPDF($arquivo);

            // Estruturar dados extraídos usando IA
            $dadosEstruturados = $this->estruturarDados($textoExtraido);

            // Salvar CV original
            $caminhoOriginal = $arquivo->store('cvs/originais', 'public');

            // Criar registro no banco
            $cvOtimizado = CvOtimizado::create([
                'candidato_id' => $candidato->id,
                'cv_original_texto' => $textoExtraido,
                'cv_original_path' => $caminhoOriginal,
                'dados_pessoais' => $dadosEstruturados['dados_pessoais'] ?? [],
                'experiencias' => $dadosEstruturados['experiencias'] ?? [],
                'skills' => $dadosEstruturados['skills'] ?? [],
                'formacao' => $dadosEstruturados['formacao'] ?? [],
                'resumo_pessoal' => $dadosEstruturados['resumo_pessoal'] ?? '',
                'otimizado_por_ia' => false
            ]);

            return [
                'success' => true,
                'cv_id' => $cvOtimizado->id,
                'dados' => $dadosEstruturados
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao extrair conteúdo do CV: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erro ao processar o CV: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Otimiza conteúdo do CV usando IA
     */
    public function otimizarComIA(int $cvId, ?string $setor = null): array
    {
        try {
            $cvOtimizado = CvOtimizado::findOrFail($cvId);

            // Preparar prompt específico para otimização
            $prompt = $this->buildOptimizationPrompt($cvOtimizado, $setor);

            // Chamar OpenAI
            $apiKey = config('openai.api_key') ?: env('OPENAI_API_KEY');

            if (!$apiKey) {
                throw new \Exception('OpenAI API Key não configurada. Configure OPENAI_API_KEY no arquivo .env');
            }

            $client = OpenAI::client($apiKey);

            $response = $client->chat()->create([
                'model' => 'gpt-4.1-nano',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Você é um especialista em RH e redação de currículos. Retorne SEMPRE e APENAS um JSON válido conforme a estrutura solicitada.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.2, // Baixa temperatura para respostas mais conservadoras
                'max_tokens' => 2000,
                'response_format' => ['type' => 'json_object']
            ]);

            $responseContent = $response->choices[0]->message->content;
            Log::info('Resposta da IA para otimização:', ['content' => $responseContent]);

            // Tentar extrair JSON da resposta (pode ter texto extra)
            $resultado = $this->decodeJsonLoose($responseContent);

            // Verificar se o JSON é válido e tem a estrutura esperada
            if (!$resultado || !is_array($resultado)) {
                Log::error('JSON inválido da IA:', ['content' => $responseContent]);
                throw new \Exception('Resposta da IA não é um JSON válido');
            }

            // Garantir estrutura mínima
            $resultado = array_merge([
                'dados_pessoais' => $cvOtimizado->dados_pessoais,
                'resumo_pessoal' => $cvOtimizado->resumo_pessoal,
                'experiencias' => $cvOtimizado->experiencias,
                'skills' => $cvOtimizado->skills,
                'formacao' => $cvOtimizado->formacao
            ], $resultado);

            // Atualizar CV com dados otimizados
            $cvOtimizado->update([
                'dados_pessoais' => $resultado['dados_pessoais'],
                'experiencias' => $resultado['experiencias'],
                'skills' => $resultado['skills'],
                'formacao' => $resultado['formacao'],
                'resumo_pessoal' => $resultado['resumo_pessoal'],
                'otimizado_por_ia' => true
            ]);

            return [
                'success' => true,
                'dados_otimizados' => $resultado
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao otimizar CV com IA: ' . $e->getMessage());

            // Em casos raros, $cvOtimizado pode não estar definido se o findOrFail falhar
            if (!isset($cvOtimizado) || !$cvOtimizado instanceof CvOtimizado) {
                $cvOtimizado = CvOtimizado::find($cvId);
                if (!$cvOtimizado) {
                    return [
                        'success' => false,
                        'error' => 'CV não encontrado para aplicar fallback de otimização.'
                    ];
                }
            }

            // Fallback: aplicar otimizações básicas sem IA
            $dadosOtimizados = $this->aplicarOtimizacaoBasica($cvOtimizado, $setor);

            // Atualizar CV com dados otimizados básicos
            $cvOtimizado->update([
                'dados_pessoais' => $dadosOtimizados['dados_pessoais'],
                'experiencias' => $dadosOtimizados['experiencias'],
                'skills' => $dadosOtimizados['skills'],
                'formacao' => $dadosOtimizados['formacao'],
                'resumo_pessoal' => $dadosOtimizados['resumo_pessoal'],
                'otimizado_por_ia' => false
            ]);

            return [
                'success' => true,
                'dados_otimizados' => $dadosOtimizados,
                'fallback' => true
            ];
        }
    }

    /**
     * Aplica otimizações básicas sem IA como fallback
     */
    private function aplicarOtimizacaoBasica(CvOtimizado $cv, ?string $setor): array
    {
        $dados = [
            'dados_pessoais' => $cv->dados_pessoais,
            'resumo_pessoal' => $cv->resumo_pessoal,
            'experiencias' => $cv->experiencias,
            'skills' => $cv->skills,
            'formacao' => $cv->formacao
        ];

        // Coletar até 6 skills planas para usar nos textos
        $flatSkills = [];
        if (is_array($dados['skills'])) {
            foreach ($dados['skills'] as $grp) {
                if (!empty($grp['habilidades']) && is_array($grp['habilidades'])) {
                    foreach ($grp['habilidades'] as $s) {
                        $s = trim((string)$s);
                        if ($s !== '') {
                            $flatSkills[] = $s;
                        }
                    }
                }
            }
        }
        $flatSkills = array_values(array_unique($flatSkills));
        $topSkills = array_slice($flatSkills, 0, 6);
        $setorKey = $this->normalizeSetor($setor);
        $setorTxt = $setorKey; // usado no texto

        // Foco e conquistas por setor (determinístico)
        $focoPorSetor = [
            'tecnologia' => 'performance, segurança e escalabilidade',
            'dados' => 'qualidade de dados, governança e analytics',
            'marketing' => 'aquisição, retenção e otimização de campanhas',
            'vendas' => 'taxa de conversão, ticket médio e ciclo de vendas',
            'finanças' => 'acurácia financeira, conformidade e redução de custos',
            'rh' => 'atração de talentos, engajamento e eficiência de processos',
            'saúde' => 'segurança do paciente, protocolos e compliance',
            'educação' => 'aprendizagem, currículo e experiência do aluno',
        ];
        $conquistasPorSetor = [
            'tecnologia' => [
                'Melhorou a performance de aplicações ao otimizar consultas e caching',
                'Aumentou a confiabilidade com automação de testes e CI/CD',
                'Elevou a segurança aplicando práticas de hardening e monitoramento'
            ],
            'dados' => [
                'Aumentou a confiabilidade dos dados com validações e qualidade de pipelines',
                'Reduziu o tempo de análises ao otimizar modelos e consultas',
                'Aprimorou a governança com catálogo e padronização de métricas'
            ],
            'marketing' => [
                'Elevou a taxa de conversão com experimentos A/B e segmentação',
                'Reduziu CAC otimizando canais e criativos',
                'Melhorou retenção ao personalizar jornadas e conteúdos'
            ],
            'vendas' => [
                'Aumentou a conversão com melhoria de qualificação e playbooks',
                'Reduziu ciclo de vendas com cadências e priorização de leads',
                'Expandiu receita com estratégias de upsell e cross-sell'
            ],
            'finanças' => [
                'Melhorou previsibilidade com rotinas de forecast e controles',
                'Reduziu custos ao renegociar contratos e otimizar despesas',
                'Fortaleceu compliance e controles internos em processos críticos'
            ],
            'rh' => [
                'Reduziu tempo de contratação com melhorias no funil de recrutamento',
                'Aumentou engajamento com rituais e planos de desenvolvimento',
                'Padronizou processos com políticas e trilhas de onboarding'
            ],
            'saúde' => [
                'Melhorou adesão a protocolos e segurança em procedimentos',
                'Reduziu tempos de atendimento com ajustes de fluxo',
                'Fortaleceu compliance com treinamentos e auditorias'
            ],
            'educação' => [
                'Aumentou aprendizagem com metodologias ativas e avaliações',
                'Melhorou satisfação ao personalizar trilhas e feedbacks',
                'Otimização curricular alinhada a competências e mercado'
            ],
        ];
        $foco = $focoPorSetor[$setorKey] ?? 'qualidade, desempenho e resultados de negócio';
        $conquistasSetor = $conquistasPorSetor[$setorKey] ?? [
                'Reduziu retrabalho ao padronizar processos e melhorar a qualidade das entregas',
                'Acelerou a entrega de funcionalidades por meio de automação e integração contínua',
                'Aprimorou a observabilidade e a confiabilidade das aplicações em produção'
            ];

        // Otimizar resumo (sempre reescreve de forma determinística)
        $nome = $dados['dados_pessoais']['nome'] ?? null;
        $role = $this->inferRoleFromExperiencias($dados['experiencias']) ?? 'profissional';
        $skillsStr = !empty($topSkills) ? implode(', ', $topSkills) : 'boas práticas e ferramentas modernas';
        $resumoBase = $dados['resumo_pessoal'] ?: '';
        $dados['resumo_pessoal'] = trim(
            ($nome ? "$nome — " : '') . ucfirst($role) . " com sólida experiência em $skillsStr. Atua no segmento de $setorTxt, entregando soluções com foco em $foco." .
            ($resumoBase ? " " . rtrim($resumoBase) : '')
        );

        // Otimizar experiências
        if (is_array($dados['experiencias'])) {
            foreach ($dados['experiencias'] as &$exp) {
                $cargo = trim((string)($exp['cargo'] ?? '')) ?: 'Profissional';
                $empresa = trim((string)($exp['empresa'] ?? '')) ?: 'Empresa';
                $periodo = trim((string)($exp['periodo'] ?? '')) ?: '';
                // Montar descrição com foco em impacto e tecnologias
                $techStr = '';
                if (!empty($topSkills)) {
                    // usar uma cópia para não mutar $topSkills global
                    $skillsCopy = $topSkills;
                    $last = array_pop($skillsCopy);
                    $techStr = ' utilizando ' . (empty($skillsCopy) ? $last : (implode(', ', $skillsCopy) . ' e ' . $last));
                }
                // descrição idempotente (não reaproveita o texto anterior para evitar duplicações)
                $exp['descricao'] = trim("$cargo em $empresa" . ($periodo ? " ($periodo)" : '') . ": liderou e implementou soluções" . $techStr . ", alinhadas ao setor $setorTxt, colaborando com times multifuncionais e aplicando boas práticas com foco em $foco.");

                // Conquistas padronizadas por setor (determinísticas)
                $exp['conquistas'] = $conquistasSetor;
            }
        }

        return $dados;
    }

    /**
     * Normaliza o setor vindo do frontend (sem/ com acento, hífens, sinônimos)
     * Para indexação em nossos mapas e para uso coerente no texto.
     */
    private function normalizeSetor(?string $setor): string
    {
        if (!$setor) {
            return 'tecnologia';
        }
        $s = trim(mb_strtolower($setor));
        // substituir hífens por espaço
        $s = str_replace(['-', '_'], ' ', $s);
        // remover acentos básicos
        $from = ['á','à','â','ã','é','è','ê','í','ì','ó','ò','ô','õ','ú','ù','ç'];
        $to   = ['a','a','a','a','e','e','e','i','i','o','o','o','o','u','u','c'];
        $sPlain = str_replace($from, $to, $s);

        // mapeamentos comuns
        $map = [
            // tecnologia
            'ti' => 'tecnologia',
            'tech' => 'tecnologia',
            'software' => 'tecnologia',
            'engenharia' => 'tecnologia',
            'tecnologia' => 'tecnologia',
            // dados
            'dados' => 'dados',
            'data' => 'dados',
            'data science' => 'dados',
            'bi' => 'dados',
            // marketing
            'marketing' => 'marketing',
            'growth' => 'marketing',
            // vendas
            'vendas' => 'vendas',
            'comercial' => 'vendas',
            'sales' => 'vendas',
            // finanças
            'financeiro' => 'financas',
            'financas' => 'finanças',
            'financas ' => 'finanças',
            'financas e controladoria' => 'finanças',
            'finance' => 'finanças',
            // rh
            'recursos humanos' => 'rh',
            'rh' => 'rh',
            'people' => 'rh',
            // saude
            'saude' => 'saúde',
            'health' => 'saúde',
            'healthcare' => 'saúde',
            // educacao
            'educacao' => 'educação',
            'education' => 'educação',
        ];

        // tentativa direta
        if (isset($map[$s])) {
            return $map[$s];
        }
        if (isset($map[$sPlain])) {
            return $map[$sPlain];
        }

        // tentativa por palavras
        foreach ($map as $k => $v) {
            if (str_contains($s, $k) || str_contains($sPlain, $k)) {
                return $v;
            }
        }

        // fallback
        return 'tecnologia';
    }

    /**
     * Extrai texto de arquivo PDF
     */
    private function extrairTextoPDF(UploadedFile $arquivo): string
    {
        $pdfPath = $arquivo->getPathname();
        return Pdf::getText($pdfPath);
    }

    /**
     * Estrutura dados extraídos usando IA
     */
    private function estruturarDados(string $texto): array
    {
        try {
            $apiKey = config('openai.api_key') ?: env('OPENAI_API_KEY');

            if (!$apiKey) {
                throw new \Exception('OpenAI API Key não configurada. Configure OPENAI_API_KEY no arquivo .env');
            }

            $client = OpenAI::client($apiKey);

            $prompt = "Analise o conteúdo de um currículo em português e extraia as informações exatamente no formato abaixo. IMPORTANTE: Retorne APENAS um JSON válido, sem comentários, sem markdown e sem texto extra. Preencha campos em branco com string vazia ou arrays vazios.
{
  \"dados_pessoais\": {\"nome\": \"\", \"email\": \"\", \"telefone\": \"\", \"linkedin\": \"\"},
  \"resumo_pessoal\": \"\",
  \"experiencias\": [{\"cargo\": \"\", \"empresa\": \"\", \"periodo\": \"\", \"descricao\": \"\", \"conquistas\": []}],
  \"skills\": [{\"categoria\": \"\", \"habilidades\": []}],
  \"formacao\": [{\"curso\": \"\", \"instituicao\": \"\", \"periodo\": \"\", \"detalhes\": \"\"}]
}

Texto do CV:
{$texto}";

            $response = $client->chat()->create([
                'model' => 'gpt-4.1-nano',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Extraia informações de currículos de forma estruturada. Retorne somente JSON válido conforme o schema.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.1, // Baixa temperatura para respostas mais conservadoras
                'max_tokens' => 1500,
                'response_format' => ['type' => 'json_object']
            ]);

            $content = $response->choices[0]->message->content;
            $decoded = $this->decodeJsonLoose($content);
            if (!$decoded) {
                throw new \Exception('Resposta da IA não pôde ser decodificada como JSON');
            }
            // Completar campos ausentes usando heurísticas do nosso template
            $completed = $this->fillMissingSections($decoded, $texto);
            return $completed;

        } catch (\Exception $e) {
            Log::error('Erro ao estruturar dados: ' . $e->getMessage());

            // Tentar extrair dados básicos do texto sem IA
            $dadosBasicos = $this->extrairDadosBasicos($texto);

            // Retornar estrutura básica em caso de erro (completando via heurísticas)
            $base = [
                'dados_pessoais' => [],
                'resumo_pessoal' => '',
                'experiencias' => [],
                'skills' => [],
                'formacao' => []
            ];
            $merged = array_merge($base, $dadosBasicos);
            return $this->fillMissingSections($merged, $texto);
        }
    }

    /**
     * Extrai dados básicos sem IA como fallback
     */
    private function extrairDadosBasicos(string $texto): array
    {
        $dados = [
            'dados_pessoais' => [
                'nome' => '',
                'email' => '',
                'telefone' => '',
                'linkedin' => ''
            ],
            'resumo_pessoal' => '',
            'experiencias' => [],
            'skills' => [],
            'formacao' => []
        ];

        // Extrair email usando regex
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $texto, $matches)) {
            $dados['dados_pessoais']['email'] = $matches[0];
        }

        // Extrair telefone usando regex
        if (preg_match('/\(?\d{2}\)?\s?\d{4,5}-?\d{4}/', $texto, $matches)) {
            $dados['dados_pessoais']['telefone'] = $matches[0];
        }

        // Extrair LinkedIn
        if (preg_match('/https?:\/\/(www\.)?linkedin\.com\/[\w\-\/]+/i', $texto, $matches)) {
            $dados['dados_pessoais']['linkedin'] = $matches[0];
        } elseif (preg_match('/linkedin\.com\/[\w\-\/]+/i', $texto, $matches)) {
            $dados['dados_pessoais']['linkedin'] = 'https://' . $matches[0];
        }

        // Extrair Nome (heurística similar à usada na análise local)
        $nome = $this->guessName($texto);
        if ($nome) {
            $dados['dados_pessoais']['nome'] = $nome;
        }

        return $dados;
    }

    /** Preenche seções vazias usando heurísticas do nosso template PDF */
    private function fillMissingSections(array $dados, string $texto): array
    {
        // Resumo
        if (empty($dados['resumo_pessoal'])) {
            $dados['resumo_pessoal'] = $this->parseResumo($texto) ?? '';
        }
        // Experiências
        if (empty($dados['experiencias'])) {
            $dados['experiencias'] = $this->parseExperiencias($texto);
        }
        // Skills
        if (empty($dados['skills'])) {
            $skillsList = $this->parseSkills($texto);
            if (!empty($skillsList)) {
                $dados['skills'] = [
                    ['categoria' => 'Competências Técnicas', 'habilidades' => $skillsList]
                ];
            }
        }
        // Formação
        if (empty($dados['formacao'])) {
            $dados['formacao'] = $this->parseFormacao($texto);
        }
        return $dados;
    }

    private function parseResumo(string $texto): ?string
    {
        if (preg_match('/RESUMO\s+PROFISSIONAL\s*(.+?)\s*(EXPERIÊNCIA|COMPETÊNCIAS|FORMAÇÃO)/si', $texto, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/PERFIL\s+PROFISSIONAL\s*(.+?)\s*(EXPERIÊNCIA|COMPETÊNCIAS|FORMAÇÃO)/si', $texto, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    private function parseExperiencias(string $texto): array
    {
        $experiencias = [];
        if (!preg_match('/EXPERIÊNCIA\s+PROFISSIONAL\s*(.+?)\s*(COMPETÊNCIAS|FORMAÇÃO|$)/si', $texto, $m)) {
            return $experiencias;
        }
        $block = trim($m[1]);
        // Separar blocos por linhas em branco
        $parts = preg_split('/\n{2,}/', $block);
        foreach ($parts as $p) {
            $lines = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $p))));
            if (count($lines) < 2) {
                continue;
            }
            $cargo = $lines[0] ?? '';
            $empresa = $lines[1] ?? '';
            $periodo = '';
            $descricao = '';
            // Detectar período em alguma linha
            foreach ($lines as $ln) {
                if (preg_match('/\d{4}.*?[—-].*?\d{4}|\d{4}.*?(Atual|Presente)/i', $ln)) {
                    $periodo = $ln;
                    continue;
                }
            }
            // Descrição: a maior linha que não é período/empresa
            foreach ($lines as $ln) {
                if ($ln === $cargo || $ln === $empresa || $ln === $periodo) {
                    continue;
                }
                if (mb_strlen($ln) > mb_strlen($descricao)) {
                    $descricao = $ln;
                }
            }
            $experiencias[] = [
                'cargo' => $cargo,
                'empresa' => $empresa,
                'periodo' => $periodo,
                'descricao' => $descricao,
                'conquistas' => []
            ];
        }
        return $experiencias;
    }

    private function parseSkills(string $texto): array
    {
        // Preferir linha "Skills: A, B, C"
        if (preg_match('/Skills\s*:\s*(.+)/i', $texto, $m)) {
            $raw = $m[1];
            // Cortar caso venha outra seção em seguida
            $raw = preg_split('/\n|COMPETÊNCIAS|FORMAÇÃO|EXPERIÊNCIA/i', $raw)[0];
            $skills = array_map(function ($s) { return trim($s); }, explode(',', $raw));
            $skills = array_values(array_filter($skills));
            return $skills;
        }
        // Parse por categorias "Categoria: item1 item2"
        $skills = [];
        if (preg_match('/COMPETÊNCIAS\s*(.+?)\s*(FORMAÇÃO|$)/si', $texto, $m)) {
            $block = $m[1];
            if (preg_match_all('/^[^:\n]{3,}:\s*(.+)$/mi', $block, $mm)) {
                foreach ($mm[1] as $list) {
                    $tokens = preg_split('/[,•\u2022\s]+/u', trim($list));
                    foreach ($tokens as $t) {
                        $t = trim($t, ",. ");
                        if ($t !== '') {
                            $skills[] = $t;
                        }
                    }
                }
            }
        }
        return array_values(array_unique($skills));
    }

    private function parseFormacao(string $texto): array
    {
        $formacao = [];
        if (!preg_match('/FORMAÇÃO\s*(.+)$/si', $texto, $m)) {
            return $formacao;
        }
        $block = trim($m[1]);
        $parts = preg_split('/\n{2,}/', $block);
        foreach ($parts as $p) {
            $lines = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $p))));
            if (count($lines) < 2) {
                continue;
            }
            $curso = $lines[0] ?? '';
            $instituicao = $lines[1] ?? '';
            $periodo = '';
            foreach ($lines as $ln) {
                if (preg_match('/\d{4}.*?[—-].*?\d{4}|\d{4}.*?(Atual|Presente)/i', $ln)) {
                    $periodo = $ln;
                    break;
                }
            }
            $formacao[] = [
                'curso' => $curso,
                'instituicao' => $instituicao,
                'periodo' => $periodo,
                'detalhes' => ''
            ];
        }
        return $formacao;
    }

    /** Infere um cargo/role principal a partir das experiências */
    private function inferRoleFromExperiencias($experiencias): ?string
    {
        if (!is_array($experiencias) || empty($experiencias)) {
            return null;
        }
        $cargos = array_filter(array_map(function ($e) { return trim((string)($e['cargo'] ?? '')); }, $experiencias));
        if (!empty($cargos)) {
            // usar o primeiro cargo com palavras chave simplificadas
            $cargo = $cargos[0];
            // normalizações simples
            $map = [
                '/full\s*stack/i' => 'desenvolvedor full stack',
                '/backend/i' => 'desenvolvedor backend',
                '/frontend/i' => 'desenvolvedor frontend',
                '/data\s*engineer/i' => 'engenheiro de dados',
                '/qa|quality/i' => 'engenheiro(a) de qualidade',
                '/bi\b/i' => 'analista de BI',
            ];
            foreach ($map as $re => $label) {
                if (preg_match($re, $cargo)) {
                    return $label;
                }
            }
            return $cargo;
        }
        return null;
    }

    /** Decodificação robusta para respostas da IA em JSON */
    private function decodeJsonLoose(?string $content): ?array
    {
        if (!$content) {
            return null;
        }
        $content = trim($content);
        // Tentativa direta
        $data = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            return $data;
        }
        // Bloco ```json
        if (preg_match('/```json\s*(\{.*?\})\s*```/s', $content, $m)) {
            $data = json_decode($m[1], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                return $data;
            }
        }
        // Procurar maior bloco entre chaves
        $start = strpos($content, '{');
        $end = strrpos($content, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $json = substr($content, $start, $end - $start + 1);
            // limpar controles
            $json = preg_replace('/[\x00-\x1F\x7F]/', '', $json);
            $json = preg_replace('/\s+/', ' ', $json);
            $data = json_decode($json, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                return $data;
            }
        }
        return null;
    }

    /** Heurística simples para nome completo */
    private function guessName(string $text): ?string
    {
        if (preg_match('/\b([A-ZÁÀÂÃÉÈÍÌÓÒÔÕÚÙÇ][a-záàâãéèíìóòôõúùç]+\s+[A-ZÁÀÂÃÉÈÍÌÓÒÔÕÚÙÇ][a-záàâãéèíìóòôõúùç]+(\s+[A-ZÁÀÂÃÉÈÍÌÓÒÔÕÚÙÇ][a-záàâãéèíìóòôõúùç]+)*)\b/', $text, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    /**
     * Constrói prompt para otimização
     */
    private function buildOptimizationPrompt(CvOtimizado $cv, ?string $setor): string
    {
        $setorText = $setor ? "para o setor de {$setor}" : "para melhor impacto geral";

        return "Você é um especialista em recursos humanos. Otimize este currículo {$setorText}. 
                    INSTRUÇÕES IMPORTANTES:
                    1. MANTENHA SEMPRE A VERACIDADE DOS DADOS (não invente experiências, empresas, cursos ou conquistas)
                    2. REESCREVA os textos para maior clareza, impacto e profissionalismo
                    3. USE verbos de ação impactantes e linguagem adequada ao setor
                    4. QUANTIFIQUE conquistas quando fizer sentido (sem inventar números)
                    5. MELHORE a estrutura e organização das informações
                    6. ADAPTE a linguagem para o setor específico
                    7. PREENCHA os campos 'descricao' e 'conquistas' nas experiências OBRIGATORIAMENTE

                    DADOS ATUAIS:
                    - Dados Pessoais: " . json_encode($cv->dados_pessoais, JSON_UNESCAPED_UNICODE) . "
                    - Resumo: {$cv->resumo_pessoal}
                    - Experiências: " . json_encode($cv->experiencias, JSON_UNESCAPED_UNICODE) . "
                    - Skills: " . json_encode($cv->skills, JSON_UNESCAPED_UNICODE) . "
                    - Formação: " . json_encode($cv->formacao, JSON_UNESCAPED_UNICODE) . "

                    EXEMPLO DE OTIMIZAÇÃO PARA EXPERIÊNCIA:
                    ANTES: {\"cargo\": \"Desenvolvedor\", \"descricao\": \"Fazia sistemas\"}
                    DEPOIS: {\"cargo\": \"Desenvolvedor Full Stack\", \"descricao\": \"Desenvolveu aplicações web escaláveis utilizando tecnologias modernas, implementando soluções que otimizaram processos e melhoraram a experiência do usuário\", \"conquistas\": [\"Implementou arquitetura de microserviços\", \"Otimizou performance do sistema em 40%\"]}

                    Retorne APENAS um JSON válido com esta estrutura EXATA:
                    {
                    \"dados_pessoais\": {\"nome\": \"\", \"email\": \"\", \"telefone\": \"\", \"linkedin\": \"\"},
                    \"resumo_pessoal\": \"Resumo profissional otimizado e impactante\",
                    \"experiencias\": [
                        {
                        \"cargo\": \"Cargo otimizado\", 
                        \"empresa\": \"Nome da empresa (não alterar)\", 
                        \"periodo\": \"Período (não alterar)\", 
                        \"descricao\": \"Descrição detalhada e otimizada das responsabilidades e atividades\", 
                        \"conquistas\": [\"Conquista específica e mensurável\", \"Outra conquista relevante\"]
                        }
                    ],
                    \"skills\": [{\"categoria\": \"Categoria organizada\", \"habilidades\": [\"Skill1\", \"Skill2\"]}],
                    \"formacao\": [{\"curso\": \"Curso (não alterar)\", \"instituicao\": \"Instituição (não alterar)\", \"periodo\": \"Período (não alterar)\", \"detalhes\": \"Detalhes otimizados\"}]
                    }";
    }
}
