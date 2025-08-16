<?php

namespace App\Http\Controllers;

use OpenAI;
use Spatie\PdfToText\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CvAnalysisController extends Controller
{
    private const MAX_FILES = 5;
    private const MAX_FILE_SIZE = 5120; // 5MB
    private const MAX_TOKENS = 1500;

    private const SKILL_POINTS = [
        // Linguagens de Programação - Pontuação Alta
        'Python' => 25,
        'JavaScript' => 20,
        'Java' => 20,
        'C#' => 20,
        'PHP' => 18,
        'TypeScript' => 18,
        'C++' => 18,
        'Go' => 18,
        'Rust' => 18,

        // Banco de Dados - Pontuação Alta
        'SQL' => 15,
        'MySQL' => 15,
        'PostgreSQL' => 15,
        'MongoDB' => 15,
        'Oracle' => 15,

        // Frameworks e Bibliotecas - Pontuação Média-Alta
        'React' => 15,
        'Angular' => 15,
        'Vue.js' => 15,
        'Node.js' => 15,
        'Express' => 15,
        'Django' => 15,
        'Flask' => 15,
        'Laravel' => 15,
        'Spring Boot' => 15,

        // DevOps e Ferramentas - Pontuação Média
        'Docker' => 12,
        'Kubernetes' => 12,
        'AWS' => 12,
        'Azure' => 12,
        'Git' => 12,
        'Jenkins' => 12,
        'Linux' => 12,

        // Metodologias e Soft Skills - Pontuação Baixa-Média
        'Scrum' => 10,
        'Agile' => 10,
        'Kanban' => 10,
        'Excel' => 8,
        'Power BI' => 10,
        'Tableau' => 10,

        // Padrão para skills não listadas
        'default' => 10
    ];

    /**
     * Endpoint principal da API para análise de currículos.
     * Recebe arquivos PDF, valida, extrai textos, envia para análise via OpenAI e retorna o resultado processado.
     * Em caso de erro, retorna mensagem apropriada e detalhes se em modo debug.
     *
     * @param Request $request Requisição HTTP contendo os arquivos dos currículos
     * @return JsonResponse Resposta JSON com análise ou erro
     */
    /**
     * @OA\Post(
     *     path="/analyze-cvs",
     *     tags={"🤖 Análise de CV"},
     *     summary="Analisar múltiplos CVs",
     *     description="Analisa até 5 CVs em PDF e retorna a pontuação e ranking dos candidatos com base nas competências encontradas",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="files",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         format="binary"
     *                     ),
     *                     description="Array de arquivos PDF (mínimo 2, máximo 5 arquivos, até 5MB cada)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Análise realizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Análise de CVs realizada com sucesso"),
     *             @OA\Property(
     *                 property="ranking",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="filename", type="string", example="cv_joao.pdf"),
     *                     @OA\Property(property="score", type="integer", example=85),
     *                     @OA\Property(property="ranking", type="integer", example=1),
     *                     @OA\Property(
     *                         property="skills",
     *                         type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="skill", type="string", example="Python"),
     *                             @OA\Property(property="points", type="integer", example=25)
     *                         )
     *                     ),
     *                     @OA\Property(property="summary", type="string", example="Candidato com forte experiência em desenvolvimento...")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Nenhum arquivo foi enviado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação de arquivo",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Erro de validação"),
     *             @OA\Property(property="details", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno do servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Erro interno do servidor"),
     *             @OA\Property(property="message", type="string", example="Erro ao processar análise com IA")
     *         )
     *     )
     * )
     */
    public function analyzeCvs(Request $request): JsonResponse
    {
        try {
            // Validação inicial
            $validationResult = $this->validateRequest($request);
            if ($validationResult !== true) {
                return $validationResult;
            }

            // Extrair textos dos PDFs
            $cvTexts = $this->extractPdfTexts($request->file('files'));
            if (isset($cvTexts['error'])) {
                return response()->json($cvTexts, 400);
            }

            // Analisar com OpenAI
            $analysisResult = $this->analyzeWithOpenAI($cvTexts);

            // Garantir que a resposta seja JSON válido
            return response()->json($analysisResult, 200, ['Content-Type' => 'application/json']);

        } catch (\Exception $e) {
            Log::error('Erro na análise de CVs: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Erro ao processar análise com IA. Tente novamente mais tarde.',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Valida a requisição recebida, garantindo que os arquivos estejam presentes e corretos.
     * Retorna true se válido, ou uma resposta JSON de erro se inválido.
     *
     * @param Request $request Requisição HTTP recebida
     * @return JsonResponse|bool true se válido, ou resposta de erro
     */
    private function validateRequest(Request $request): JsonResponse|bool
    {
        if (!$request->hasFile('files')) {
            return response()->json([
                'error' => 'Nenhum arquivo foi enviado'
            ], 400);
        }

        try {
            $request->validate([
                'files' => 'required|array|min:2|max:' . self::MAX_FILES,
                'files.*' => 'required|file|mimes:pdf|max:' . self::MAX_FILE_SIZE,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Erro de validação',
                'details' => $e->errors()
            ], 422);
        }

        return true;
    }

    /**
     * Extrai o texto de cada arquivo PDF enviado na requisição.
     * Retorna um array com os textos extraídos ou um erro se algum PDF estiver ilegível.
     *
     * @param array $files Lista de arquivos PDF
     * @return array Array de textos extraídos ou erro
     */
    private function extractPdfTexts(array $files): array
    {
        $cvTexts = [];

        foreach ($files as $file) {
            try {
                $pdfPath = $file->getPathname();
                $text = Pdf::getText($pdfPath);

                // Verificar se o texto foi extraído com sucesso
                if (empty(trim($text))) {
                    Log::warning('PDF vazio ou não legível: ' . $file->getClientOriginalName());
                    return [
                        'error' => 'PDF vazio ou não legível: ' . $file->getClientOriginalName()
                    ];
                }

                $cvTexts[] = [
                    'filename' => $file->getClientOriginalName(),
                    'text' => $this->sanitizeText($text)
                ];

            } catch (\Exception $e) {
                Log::error('Erro ao processar PDF: ' . $file->getClientOriginalName(), [
                    'error' => $e->getMessage()
                ]);

                return [
                    'error' => 'Erro ao processar PDF: ' . $file->getClientOriginalName(),
                    'details' => $e->getMessage()
                ];
            }
        }

        return $cvTexts;
    }

    /**
     * Limpa e normaliza o texto extraído dos PDFs, removendo espaços e limitando tamanho.
     *
     * @param string $text Texto extraído do PDF
     * @return string Texto sanitizado
     */
    private function sanitizeText(string $text): string
    {
        // Remove caracteres especiais e normaliza espaços
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        // Limita o tamanho do texto para evitar problemas com a API
        if (strlen($text) > 10000) {
            $text = substr($text, 0, 10000) . '...';
        }

        return $text;
    }

    /**
     * Envia os textos dos currículos para a OpenAI, processa a resposta e retorna os dados analisados.
     * Em caso de erro na comunicação, retorna dados de fallback.
     *
     * @param array $cvTexts Textos dos currículos extraídos
     * @return array Dados analisados ou fallback
     */
    private function analyzeWithOpenAI(array $cvTexts): array
    {
        $combinedText = $this->prepareCombinedText($cvTexts);
        $prompt = $this->buildPrompt($combinedText);

        try {
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
                        'content' => 'Você é um especialista em análise de currículos. Retorne SEMPRE e APENAS um JSON válido, sem texto adicional.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ],
                ],
                'temperature' => 0.1,
                'max_tokens' => self::MAX_TOKENS,
                'response_format' => ['type' => 'json_object']
            ]);

            $result = $response->choices[0]->message->content;

            // Log da resposta para debug
            Log::info('Resposta da OpenAI:', ['response' => $result]);

            return $this->parseAIResponse($result);

        } catch (\Exception $e) {
            Log::error('Erro ao comunicar com OpenAI: ' . $e->getMessage());

            // Fallback determinístico local (sem IA)
            Log::warning('Usando análise local determinística (sem OpenAI)');
            return $this->analyzeLocally($cvTexts);
        }
    }

    /**
     * Monta o texto combinado de todos os currículos para ser enviado no prompt da IA.
     *
     * @param array $cvTexts Textos dos currículos
     * @return string Texto combinado para análise
     */
    private function prepareCombinedText(array $cvTexts): string
    {
        $combinedText = "Currículos para análise:\n\n";

        foreach ($cvTexts as $index => $cv) {
            $combinedText .= "=== CURRÍCULO " . ($index + 1) . " - {$cv['filename']} ===\n";
            $combinedText .= $cv['text'] . "\n\n";
        }

        return $combinedText;
    }

    /**
     * Monta o prompt detalhado para a IA, incluindo regras de análise e sistema de pontuação.
     *
     * @param string $combinedText Texto dos currículos já combinado
     * @return string Prompt completo para a IA
     */
    private function buildPrompt(string $combinedText): string
    {
        $skillPoints = json_encode(self::SKILL_POINTS);

        return "Analise os currículos fornecidos e extraia as informações seguindo estas regras PRECISAS:

    1. INFORMAÇÕES BÁSICAS:
    - Nome completo do candidato
    - Email (formato: nome@dominio.com)

    2. IDENTIFICAÇÃO DE SKILLS TÉCNICAS:

    REGRAS RÍGIDAS PARA IDENTIFICAÇÃO:
    - Procure APENAS por skills técnicas, ferramentas e tecnologias específicas
    - Ignore completamente: soft skills, idiomas, certificações genéricas
    - Use EXATAMENTE os nomes da tabela de pontuação quando encontrar essas tecnologias
    - Para skills similares, use o nome padrão:
    * React.js ou ReactJS → React
    * Node ou NodeJS → Node.js

    SKILLS A IGNORAR (não pontuar):
    - Comunicação, Liderança, Trabalho em equipe
    - Inglês, Português, Espanhol
    - Graduação, MBA, Certificado
    - Anos de experiência
    - Empresas onde trabalhou

    PROCESSO DE IDENTIFICAÇÃO:
    1. Leia o CV linha por linha
    2. Identifique apenas tecnologias, linguagens, frameworks, ferramentas
    3. Normalize os nomes conforme a tabela
    4. Conte quantas vezes cada skill aparece no CV (para frequência)
    5. Para pontuação, conte cada skill única apenas UMA vez

    3. SISTEMA DE PONTUAÇÃO DETERMINÍSTICO:

    IMPORTANTE: A pontuação DEVE ser calculada de forma EXATA e REPRODUZÍVEL usando APENAS esta tabela:

    TABELA DE PONTUAÇÃO (use EXATAMENTE estes valores):
    • Linguagens de Programação:
    - Python = 25 pontos
    - Java = 20 pontos

    • Banco de Dados:
    - SQL = 15 pontos

    • Frameworks e Bibliotecas:
    - React = 15 pontos
    - Node.js = 15 pontos
    - Flutter = 15 pontos
    - Firebase = 15 pontos

    • DevOps e Ferramentas:
    - Docker = 12 pontos
    - Kubernetes = 12 pontos
    - AWS = 12 pontos
    - Azure = 12 pontos
    - Git = 12 pontos
    - Jenkins = 12 pontos
    - Linux = 12 pontos

    • Metodologias e Business:
    - Power BI = 10 pontos
    - Tableau = 10 pontos
    - Excel = 8 pontos

    • Qualquer outra skill técnica = 10 pontos

    REGRAS OBRIGATÓRIAS DE CÁLCULO:
    1. Identifique APENAS skills técnicas (ignore soft skills como 'comunicação', 'liderança')
    2. Use os nomes EXATOS da tabela (case-insensitive)
    3. Conte cada skill ÚNICA apenas UMA vez por candidato
    4. Some os pontos de todas as skills únicas encontradas
    5. Se a soma exceder 100 pontos, limite a 100
    6. NÃO adicione pontos subjetivos ou bônus arbitrários
    7. A pontuação DEVE ser IDÊNTICA se o mesmo CV for analisado novamente

    EXEMPLO DE CÁLCULO CORRETO:
    - CV com: Python, Node.js, React, Docker, Excel
    - Cálculo: 25 + 15 + 15 + 12 + 8 = 75 pontos
    - Resultado: 75 pontos (não arredondar ou ajustar)

    4. ANÁLISE COMPARATIVA CRITERIOSA:

    IMPORTANTE: Para classificar corretamente as skills:

    A) SKILLS COMUNS: 
    - Uma skill é COMUM se aparece em PELO MENOS 2 candidatos
    - Exemplo: Se 'Python' aparece em Pedro e Lucas, é uma skill comum
    - Se 'React' aparece em Lucas e Ana, é uma skill comum

    B) SKILLS EXCLUSIVAS:
    - Uma skill é EXCLUSIVA se aparece em APENAS 1 candidato
    - Verifique cuidadosamente: se uma skill aparece em mais de um CV, NÃO pode ser exclusiva
    - Exemplo: Se 'Power BI' aparece APENAS em Pedro, então é exclusiva dele
    - Se 'Power BI' aparece em Pedro E Ana, então NÃO é exclusiva de nenhum

    PROCESSO DE VERIFICAÇÃO:
    1. Liste todas as skills de todos os candidatos
    2. Para cada skill, conte em quantos candidatos ela aparece
    3. Se aparece em 2+ candidatos → vai para skills_comuns
    4. Se aparece em apenas 1 candidato → vai para skills_unicas desse candidato

    Retorne um JSON válido no seguinte formato exato:
    {
    \"candidatos\": [
        {
        \"nome\": \"Nome do Candidato\",
        \"email\": \"email@dominio.com\",
        \"skills\": [
            {\"skill\": \"Nome da Skill\", \"frequencia\": 1}
        ],
        \"pontuacao\": 45
        }
    ],
    \"comparacao\": {
        \"skills_comuns\": [\"skill1\", \"skill2\"],
        \"skills_unicas\": {
        \"Nome Candidato 1\": [\"skill_exclusiva1\"],
        \"Nome Candidato 2\": [\"skill_exclusiva2\"]
        }
    }
    }

    Currículos:
    {$combinedText}";
    }


    /**
     * Processa a resposta da IA, tentando extrair e validar o JSON retornado.
     * Se não conseguir extrair um JSON válido, retorna dados de fallback.
     *
     * @param string $result Resposta bruta da IA
     * @return array Dados analisados ou fallback
     */
    private function parseAIResponse(string $result): array
    {
        // Limpar e normalizar a resposta da IA
        $result = trim($result);

        // Log da resposta original para debug
        Log::info('Resposta original da IA:', ['response' => $result]);

        // Tentar decodificar diretamente como JSON
        $analysisData = json_decode($result, true);

        if (json_last_error() === JSON_ERROR_NONE && $analysisData) {
            return $this->validateAnalysisData($analysisData);
        }

        // Tentar extrair JSON de blocos markdown
        if (preg_match('/```json\s*(\{.*?\})\s*```/s', $result, $matches)) {
            $jsonContent = trim($matches[1]);
            $analysisData = json_decode($jsonContent, true);
            if (json_last_error() === JSON_ERROR_NONE && $analysisData) {
                return $this->validateAnalysisData($analysisData);
            }
        }

        // Tentar encontrar JSON no meio do texto (busca mais robusta)
        $jsonStart = strpos($result, '{');
        $jsonEnd = strrpos($result, '}');

        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonContent = substr($result, $jsonStart, $jsonEnd - $jsonStart + 1);

            // Limpar caracteres invisíveis e quebras de linha desnecessárias
            $jsonContent = preg_replace('/[\x00-\x1F\x7F]/', '', $jsonContent);
            $jsonContent = preg_replace('/\s+/', ' ', $jsonContent);

            $analysisData = json_decode($jsonContent, true);

            if (json_last_error() === JSON_ERROR_NONE && $analysisData) {
                return $this->validateAnalysisData($analysisData);
            }
        }

        // Se ainda não conseguiu, tentar uma abordagem mais agressiva
        $cleanedResult = $this->cleanJsonString($result);
        $analysisData = json_decode($cleanedResult, true);

        if (json_last_error() === JSON_ERROR_NONE && $analysisData) {
            return $this->validateAnalysisData($analysisData);
        }

        Log::error('Não foi possível extrair JSON válido da resposta da IA', [
            'response' => $result,
            'json_error' => json_last_error_msg()
        ]);

        // Retornar dados padrão ao invés de falhar
        return $this->generateFallbackData();
    }

    /**
     * Limpa uma string potencialmente contendo JSON, removendo caracteres de controle e espaços extras.
     * Tenta extrair apenas a parte JSON da string.
     *
     * @param string $jsonString String possivelmente contendo JSON
     * @return string String JSON limpa
     */
    private function cleanJsonString(string $jsonString): string
    {
        // Remove caracteres de controle e quebras de linha desnecessárias
        $jsonString = preg_replace('/[\x00-\x1F\x7F]/', '', $jsonString);

        // Remove espaços extras
        $jsonString = preg_replace('/\s+/', ' ', $jsonString);

        // Tenta encontrar e extrair apenas a parte JSON
        if (preg_match('/\{.*\}/s', $jsonString, $matches)) {
            return trim($matches[0]);
        }

        return trim($jsonString);
    }

    /**
     * Gera e retorna dados simulados de fallback para análise de currículos.
     *
     * Esta função é utilizada quando ocorre algum erro na análise real dos CVs
     * (ex: falha na comunicação com a OpenAI ou resposta inválida), garantindo
     * que a API sempre retorne um JSON válido para o frontend.
     *
     * @return array Estrutura de dados simulada para candidatos e comparação de skills.
     */
    private function generateFallbackData(): array
    {
        Log::warning('Gerando dados de fallback para análise de CVs');

        return [
            'candidatos' => [
                [
                    'nome' => 'Candidato 1',
                    'email' => 'candidato1@email.com',
                    'pontuacao' => 65,
                    'skills' => [
                        ['skill' => 'Microsoft Office', 'frequencia' => 2],
                        ['skill' => 'Comunicação', 'frequencia' => 1],
                        ['skill' => 'Gestão de projetos', 'frequencia' => 1]
                    ]
                ],
                [
                    'nome' => 'Candidato 2',
                    'email' => 'candidato2@email.com',
                    'pontuacao' => 55,
                    'skills' => [
                        ['skill' => 'Excel', 'frequencia' => 2],
                        ['skill' => 'Comunicação', 'frequencia' => 1],
                        ['skill' => 'Análise de dados', 'frequencia' => 1]
                    ]
                ]
            ],
            'comparacao' => [
                'skills_comuns' => ['Comunicação'],
                'skills_unicas' => [
                    'Candidato 1' => ['Gestão de projetos'],
                    'Candidato 2' => ['Análise de dados']
                ]
            ]
        ];
    }

    /**
     * Valida e corrige a estrutura dos dados retornados pela análise dos CVs.
     *
     * Garante que o array de candidatos e a seção de comparação estejam presentes
     * e no formato esperado. Se houver inconsistências ou dados faltando, retorna
     * dados de fallback. Também força a revalidação das skills e pontuações.
     *
     * @param array $data Dados retornados pela IA ou fallback
     * @return array Dados validados e corrigidos
     */
    private function validateAnalysisData(array $data): array
    {
        // Validação básica da estrutura
        if (!isset($data['candidatos']) || !is_array($data['candidatos'])) {
            Log::error('Estrutura de dados inválida: candidatos não encontrados');
            return $this->generateFallbackData();
        }

        if (!isset($data['comparacao'])) {
            $data['comparacao'] = [
                'skills_comuns' => [],
                'skills_unicas' => []
            ];
        }

        // Validar cada candidato
        foreach ($data['candidatos'] as &$candidato) {
            if (!isset($candidato['nome']) || !isset($candidato['email'])) {
                Log::error('Dados incompletos do candidato', ['candidato' => $candidato]);
                return $this->generateFallbackData();
            }

            if (!isset($candidato['skills']) || !is_array($candidato['skills'])) {
                $candidato['skills'] = [];
            }

            if (!isset($candidato['pontuacao']) || !is_numeric($candidato['pontuacao'])) {
                $candidato['pontuacao'] = 0;
            }

            // Garantir que cada skill tenha a estrutura correta
            foreach ($candidato['skills'] as &$skill) {
                if (!isset($skill['skill']) || !isset($skill['frequencia'])) {
                    $skill = ['skill' => 'Skill não identificada', 'frequencia' => 1];
                }
            }
        }

        // CORREÇÃO DA LÓGICA DE COMPARAÇÃO
        $data['comparacao'] = $this->recalculateSkillComparison($data['candidatos']);

        // CORREÇÃO E VALIDAÇÃO DA PONTUAÇÃO
        $data['candidatos'] = $this->recalculateScores($data['candidatos']);

        return $data;
    }

    /**
     * Recalcula corretamente as skills comuns e exclusivas entre os candidatos.
     *
     * @param array $candidatos Lista de candidatos com suas skills
     * @return array Estrutura com skills comuns e skills únicas por candidato
     */
    private function recalculateSkillComparison(array $candidatos): array
    {
        // Coletar todas as skills de todos os candidatos
        $skillsByCandidate = [];
        $allSkills = [];

        foreach ($candidatos as $candidato) {
            $candidateName = $candidato['nome'];
            $skillsByCandidate[$candidateName] = [];

            foreach ($candidato['skills'] as $skillData) {
                $skillName = $skillData['skill'];
                $skillsByCandidate[$candidateName][] = $skillName;
                $allSkills[] = $skillName;
            }
        }

        // Contar quantas vezes cada skill aparece
        $skillCounts = array_count_values($allSkills);

        // Separar skills comuns (aparecem em 2+ candidatos) e exclusivas (apenas 1 candidato)
        $skillsComuns = [];
        $skillsUnicas = [];

        // Inicializar array de skills únicas para cada candidato
        foreach ($candidatos as $candidato) {
            $skillsUnicas[$candidato['nome']] = [];
        }

        foreach ($skillCounts as $skill => $count) {
            if ($count >= 2) {
                // Skill aparece em 2 ou mais candidatos → é comum
                $skillsComuns[] = $skill;
            } else {
                // Skill aparece em apenas 1 candidato → é exclusiva
                // Encontrar qual candidato tem essa skill
                foreach ($skillsByCandidate as $candidateName => $candidateSkills) {
                    if (in_array($skill, $candidateSkills)) {
                        $skillsUnicas[$candidateName][] = $skill;
                        break;
                    }
                }
            }
        }

        // Remover candidatos sem skills únicas do array
        $skillsUnicas = array_filter($skillsUnicas, function ($skills) {
            return !empty($skills);
        });

        Log::info('Skills recalculadas:', [
            'skills_comuns' => $skillsComuns,
            'skills_unicas' => $skillsUnicas,
            'skill_counts' => $skillCounts
        ]);

        return [
            'skills_comuns' => array_values(array_unique($skillsComuns)),
            'skills_unicas' => $skillsUnicas
        ];
    }

    /**
     * Recalcula as pontuações de forma determinística baseada nas skills
     */
    private function recalculateScores(array $candidatos): array
    {
        foreach ($candidatos as &$candidato) {
            $pontuacao = 0;
            $skillsEncontradas = [];

            // Coletar e normalizar skills únicas do candidato
            foreach ($candidato['skills'] as &$skillData) {
                $skillName = $this->normalizeSkillName($skillData['skill']);
                $skillData['skill'] = $skillName; // Atualizar o nome normalizado

                if (!in_array($skillName, $skillsEncontradas)) {
                    $skillsEncontradas[] = $skillName;
                }
            }

            // Calcular pontuação baseada na tabela de pontos
            foreach ($skillsEncontradas as $skill) {
                if (isset(self::SKILL_POINTS[$skill])) {
                    $pontuacao += self::SKILL_POINTS[$skill];
                } else {
                    $pontuacao += self::SKILL_POINTS['default'];
                }
            }

            // Limitar a 100 pontos
            if ($pontuacao > 100) {
                $pontuacao = 100;
            }

            $candidato['pontuacao'] = $pontuacao;

            Log::info("Pontuação recalculada para {$candidato['nome']}: {$pontuacao} pontos", [
                'skills' => $skillsEncontradas,
                'detalhes_pontuacao' => array_map(function ($skill) {
                    return [
                        'skill' => $skill,
                        'pontos' => self::SKILL_POINTS[$skill] ?? self::SKILL_POINTS['default']
                    ];
                }, $skillsEncontradas)
            ]);
        }

        return $candidatos;
    }

    /**
     * Normaliza nomes de skills para padronização
     */
    private function normalizeSkillName(string $skill): string
    {
        $skill = trim($skill);

        // Mapeamento de variações para nomes padrão
        $skillMapping = [
            'java' => 'Java',
            'node' => 'Node.js',
            'nodejs' => 'Node.js',
            'flutter' => 'Flutter',
            'firebase' => 'Firebase',
            'git' => 'Git',
            'react.js' => 'React',
            'reactjs' => 'React',
            'python' => 'Python',
            'sql' => 'SQL',
            'power bi' => 'Power BI',
            'powerbi' => 'Power BI',
            'tableau' => 'Tableau',
            'excel' => 'Excel',
            'aws' => 'AWS',
            'kubernetes' => 'Kubernetes',
            'jenkins' => 'Jenkins',
            'linux' => 'Linux',
            'docker' => 'Docker',
            'azure' => 'Azure',
        ];

        $lowerSkill = strtolower($skill);

        if (isset($skillMapping[$lowerSkill])) {
            return $skillMapping[$lowerSkill];
        }

        // Capitalizar a primeira letra se não encontrou mapeamento
        return ucfirst($skill);
    }

    /**
     * Análise local determinística (sem OpenAI):
     * - Extrai skills por varredura de palavras-chave/sinônimos
     * - Conta frequências por CV
     * - Recalcula pontuação e comparação usando as rotinas existentes
     */
    private function analyzeLocally(array $cvTexts): array
    {
        // Tabela de sinônimos -> nome normalizado
        $synonyms = [
            'reactjs' => 'React', 'react.js' => 'React', 'react' => 'React',
            'nodejs' => 'Node.js', 'node.js' => 'Node.js', 'node' => 'Node.js',
            'ts' => 'TypeScript', 'typescript' => 'TypeScript',
            'js' => 'JavaScript', 'javascript' => 'JavaScript',
            'py' => 'Python', 'python' => 'Python',
            'java' => 'Java', 'c#' => 'C#', 'c++' => 'C++', 'php' => 'PHP', 'go' => 'Go', 'golang' => 'Go', 'rust' => 'Rust',
            'sql' => 'SQL', 'mysql' => 'MySQL', 'postgres' => 'PostgreSQL', 'postgresql' => 'PostgreSQL', 'mongodb' => 'MongoDB', 'oracle' => 'Oracle',
            'express' => 'Express', 'django' => 'Django', 'flask' => 'Flask', 'laravel' => 'Laravel', 'spring boot' => 'Spring Boot', 'springboot' => 'Spring Boot',
            'docker' => 'Docker', 'kubernetes' => 'Kubernetes', 'aws' => 'AWS', 'azure' => 'Azure', 'git' => 'Git', 'jenkins' => 'Jenkins', 'linux' => 'Linux',
            'power bi' => 'Power BI', 'tableau' => 'Tableau', 'excel' => 'Excel',
            'vue' => 'Vue.js', 'vue.js' => 'Vue.js'
        ];

        // Construir lista de skills conhecidas a partir da tabela de pontos
        $knownSkills = array_diff(array_keys(self::SKILL_POINTS), ['default']);
        $normalizedPatterns = [];
        foreach ($knownSkills as $s) {
            $normalizedPatterns[strtolower($s)] = $s; // já normalizado
        }

        $candidatos = [];

        foreach ($cvTexts as $cv) {
            $text = strtolower($cv['text'] ?? '');
            $filename = $cv['filename'] ?? 'cv.pdf';

            // Extrair email
            $email = $this->extractEmail($cv['text'] ?? '') ?? 'sem-email@exemplo.com';
            // Extrair nome (do texto ou do filename)
            $nome = $this->extractName($cv['text'] ?? '', $filename);

            // Contar frequências
            $freqMap = [];

            // Primeiro, procurar skills conhecidas pelo nome exato (case-insensitive)
            foreach ($normalizedPatterns as $lower => $normalized) {
                $pattern = '/' . preg_quote($lower, '/') . '(?![a-z0-9\-\.])/i';
                if (preg_match_all($pattern, $text, $m)) {
                    $freqMap[$normalized] = ($freqMap[$normalized] ?? 0) + count($m[0]);
                }
            }

            // Depois, capturar sinônimos
            foreach ($synonyms as $syn => $normalized) {
                $pattern = '/' . preg_quote($syn, '/') . '(?![a-z0-9\-\.])/i';
                if (preg_match_all($pattern, $text, $m)) {
                    $freqMap[$normalized] = ($freqMap[$normalized] ?? 0) + count($m[0]);
                }
            }

            // Montar lista de skills com frequência (apenas encontradas)
            $skills = [];
            foreach ($freqMap as $skillName => $freq) {
                if ($freq > 0) {
                    $skills[] = ['skill' => $this->normalizeSkillName($skillName), 'frequencia' => $freq];
                }
            }

            $candidatos[] = [
                'nome' => $nome,
                'email' => $email,
                'skills' => $skills,
                'pontuacao' => 0,
            ];
        }

        // Recalcular pontuações e comparação
        $candidatos = $this->recalculateScores($candidatos);
        $comparacao = $this->recalculateSkillComparison($candidatos);

        return [
            'candidatos' => $candidatos,
            'comparacao' => $comparacao,
        ];
    }

    private function extractEmail(string $text): ?string
    {
        if (preg_match('/[\w\.+\-]+@[\w\.-]+\.[a-zA-Z]{2,}/', $text, $m)) {
            return $m[0];
        }
        return null;
    }

    private function extractName(string $text, string $filename): string
    {
        // Tentar capturar primeira linha com 2+ palavras capitalizadas
        if (preg_match('/\b([A-ZÁÀÂÃÉÈÍÌÓÒÔÕÚÙÇ][a-záàâãéèíìóòôõúùç]+\s+[A-ZÁÀÂÃÉÈÍÌÓÒÔÕÚÙÇ][a-záàâãéèíìóòôõúùç]+(\s+[A-ZÁÀÂÃÉÈÍÌÓÒÔÕÚÙÇ][a-záàâãéèíìóòôõúùç]+)*)\b/', $text, $m)) {
            return trim($m[1]);
        }
        // Fallback: derivar do filename (ex.: cv_ana-costa_*.pdf)
        $base = strtolower(pathinfo($filename, PATHINFO_FILENAME));
        $base = preg_replace('/^cv[_\-]?/', '', $base);
        $base = preg_replace('/_\d+$/', '', $base);
        $base = str_replace(['_', '-'], ' ', $base);
        return ucwords(trim($base)) ?: 'Candidato';
    }
}
