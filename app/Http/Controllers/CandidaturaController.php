<?php

namespace App\Http\Controllers;

use App\Models\Candidatura;
use App\Models\Oportunidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\NovaCandidaturaAdmin;
use App\Mail\ConfirmacaoCandidatura;
use App\Services\ResendService;
use App\Notifications\NovaCandidaturaNotification;
use Exception;

class CandidaturaController extends Controller
{
    protected $resendService;

    public function __construct(ResendService $resendService)
    {
        $this->resendService = $resendService;
    }
    /**
     * @OA\Post(
     *     path="/candidaturas",
     *     tags={"📄 Candidaturas"},
     *     summary="Submeter candidatura",
     *     description="Submete uma candidatura para uma oportunidade específica",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"oportunidade_id","telefone","cv_file","rgpd","recaptcha_token"},
     *                 @OA\Property(property="oportunidade_id", type="integer", example=1),
     *                 @OA\Property(property="nome", type="string", example="João Silva", description="Obrigatório se não estiver autenticado"),
     *                 @OA\Property(property="email", type="string", format="email", example="joao@exemplo.com", description="Obrigatório se não estiver autenticado"),
     *                 @OA\Property(property="telefone", type="string", example="+351912345678"),
     *                 @OA\Property(property="linkedin", type="string", format="url", example="https://linkedin.com/in/joaosilva"),
     *                 @OA\Property(property="cv_file", type="string", format="binary", description="Arquivo CV (PDF, DOC, DOCX - máx 5MB)"),
     *                 @OA\Property(property="rgpd", type="boolean", example=true, description="Consentimento RGPD"),
     *                 @OA\Property(property="recaptcha_token", type="string", example="03AGdBq25..."),
     *                 @OA\Property(property="convite_token", type="string", example="abc123", description="Token de convite (opcional)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Candidatura submetida com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Candidatura submetida com sucesso!"),
     *             @OA\Property(
     *                 property="candidatura",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nome", type="string", example="João Silva"),
     *                 @OA\Property(property="slug", type="string", example="joao-silva-1")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Oportunidade inativa ou já candidatado",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Esta oportunidade não está mais ativa")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        // Obter usuário autenticado (pode ser null se não estiver logado)
        $user = $request->user();
        
        $validationRules = [
            'oportunidade_id' => 'required|exists:oportunidades,id',
            'telefone' => 'required|string|max:20',
            'cv_file' => 'required|file|mimes:pdf,doc,docx|max:5120', // 5MB
            'rgpd' => 'required|accepted',
            'recaptcha_token' => 'required|string',
            'convite_token' => 'sometimes|string' // Token de convite opcional
        ];

        // Se não estiver autenticado, validar campos adicionais
        if (!$user) {
            $validationRules['nome'] = 'required|string|max:100';
            $validationRules['email'] = 'required|email|max:255';
            $validationRules['linkedin'] = 'nullable|url|max:255';
        }

        $validated = $request->validate($validationRules);

        // Verificar se a oportunidade está ativa
        $oportunidade = Oportunidade::findOrFail($validated['oportunidade_id']);
        if (!$oportunidade->ativa) {
            return response()->json(['message' => 'Esta oportunidade não está mais ativa.'], 400);
        }

        // Upload do CV
        $cvPath = $request->file('cv_file')->store('cvs', 'public');

        // Extração real de skills do CV usando análise de texto
        $skillsExtraidas = $this->extrairSkillsDoCV($request->file('cv_file'), $oportunidade->skills_desejadas);

        // Criar candidatura com os dados do usuário autenticado ou campos do formulário
        $candidaturaData = [
            'oportunidade_id' => $validated['oportunidade_id'],
            'telefone' => $validated['telefone'],
            'cv_path' => $cvPath,
            'rgpd_aceito' => true,
            'skills_extraidas' => $skillsExtraidas
        ];

        if ($user) {
            // Usuário autenticado - usar dados do perfil
            $candidaturaData['nome'] = $user->name;
            $candidaturaData['apelido'] = explode(' ', $user->name)[0] ?? $user->name;
            $candidaturaData['email'] = $user->email;
            $candidaturaData['linkedin'] = $user->profile_url ?? null;
        } else {
            // Usuário não autenticado - usar dados do formulário
            $candidaturaData['nome'] = $validated['nome'];
            $candidaturaData['apelido'] = explode(' ', $validated['nome'])[0] ?? $validated['nome'];
            $candidaturaData['email'] = $validated['email'];
            $candidaturaData['linkedin'] = $validated['linkedin'] ?? null;
        }

        $candidatura = Candidatura::create($candidaturaData);

        $candidatura->atualizarPontuacao();
        
        // Se foi candidatura através de convite, marcar no convite
        if (isset($validated['convite_token'])) {
            $convite = \App\Models\ConviteCandidato::where('token', $validated['convite_token'])->first();
            if ($convite) {
                $convite->update([
                    'candidatou_se' => true,
                    'visualizado_em' => $convite->visualizado_em ?: now()
                ]);
            }
        }

        // Adicionar candidato ao grupo de audiência do Resend
        $this->adicionarCandidatoAoGrupoDeAudiencia($candidatura);

        // Carregar relacionamento para os emails
        $candidatura->load('oportunidade');

        // Enviar emails
        try {
            // Email para o admin
            Mail::to(config('mail.admin_email', env('MAIL_ADMIN_EMAIL', 'lucas.rodrigues@team.inovcorp.com')))
                ->send(new NovaCandidaturaAdmin($candidatura));

            // Email de confirmação para o candidato
            Mail::to($candidatura->email)
                ->send(new ConfirmacaoCandidatura($candidatura));
                
            // Notificar a empresa sobre a nova candidatura
            if ($candidatura->oportunidade->company) {
                $candidatura->oportunidade->company->notify(new NovaCandidaturaNotification($candidatura));
            }
        } catch (Exception $e) {
            // Log do erro mas não interrompe o processo
            Log::error('Erro ao enviar email: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Candidatura enviada com sucesso!',
            'data' => $candidatura
        ], 201);
    }

    public function index(Request $request)
    {
        // Obter apenas candidaturas das oportunidades da empresa logada
        $company = $request->user();
        $oportunidadeIds = $company->oportunidades()->pluck('id');

        $query = Candidatura::with('oportunidade')
            ->whereIn('oportunidade_id', $oportunidadeIds)
            ->orderBy('pontuacao_skills', 'desc')
            ->orderBy('created_at', 'desc');

        // Filtrar por oportunidade se especificado (apenas se pertencer à empresa)
        if ($request->has('oportunidade_id')) {
            $oportunidadeId = $request->oportunidade_id;
            if ($oportunidadeIds->contains($oportunidadeId)) {
                $query->where('oportunidade_id', $oportunidadeId);
            }
        }

        $candidaturas = $query->paginate(10);

        return response()->json($candidaturas);
    }

    public function show(Request $request, Candidatura $candidatura)
    {
        // Verificar se a candidatura pertence a uma oportunidade da empresa logada
        $company = $request->user();
        $oportunidadeIds = $company->oportunidades()->pluck('id');
        
        if (!$oportunidadeIds->contains($candidatura->oportunidade_id)) {
            return response()->json([
                'message' => 'Acesso negado. Esta candidatura não pertence às suas oportunidades.'
            ], 403);
        }

        $candidatura->load('oportunidade');
        return response()->json($candidatura);
    }

    public function candidaturasPorOportunidade(Request $request, $oportunidadeId)
    {
        // Verificar se a oportunidade pertence à empresa logada
        $company = $request->user();
        $oportunidade = $company->oportunidades()->findOrFail($oportunidadeId);
        
        $candidaturas = Candidatura::with('oportunidade')
            ->where('oportunidade_id', $oportunidadeId)
            ->orderBy('pontuacao_skills', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'oportunidade' => $oportunidade,
            'candidaturas' => $candidaturas
        ]);
    }

    public function atualizarSkills(Request $request, Candidatura $candidatura)
    {
        // Verificar se a candidatura pertence a uma oportunidade da empresa logada
        $company = $request->user();
        $oportunidadeIds = $company->oportunidades()->pluck('id');
        
        if (!$oportunidadeIds->contains($candidatura->oportunidade_id)) {
            return response()->json([
                'message' => 'Acesso negado. Esta candidatura não pertence às suas oportunidades.'
            ], 403);
        }

        $validated = $request->validate([
            'skills_extraidas' => 'required|array',
            'skills_extraidas.*' => 'string'
        ]);

        $candidatura->update([
            'skills_extraidas' => $validated['skills_extraidas']
        ]);
        
        $candidatura->atualizarPontuacao();

        return response()->json([
            'message' => 'Skills atualizadas com sucesso!',
            'candidatura' => $candidatura->fresh()
        ]);
    }

    /**
     * Remove a candidatura pelo slug.
     */
    public function destroyBySlug(Request $request, $slug)
    {
        $candidatura = Candidatura::where('slug', $slug)->firstOrFail();
        
        // Verificar se a candidatura pertence a uma oportunidade da empresa logada
        $company = $request->user();
        $oportunidadeIds = $company->oportunidades()->pluck('id');
        
        if (!$oportunidadeIds->contains($candidatura->oportunidade_id)) {
            return response()->json([
                'message' => 'Acesso negado. Esta candidatura não pertence às suas oportunidades.'
            ], 403);
        }
        
        // Remove o arquivo CV do storage
        if ($candidatura->cv_path && Storage::disk('public')->exists($candidatura->cv_path)) {
            Storage::disk('public')->delete($candidatura->cv_path);
        }
        
        $candidatura->delete();
        
        return response()->json([
            'message' => 'Candidatura removida com sucesso!'
        ]);
    }

    /**
     * Atualizar avaliação da empresa para uma candidatura.
     */
    public function updateRating(Request $request, Candidatura $candidatura)
    {
        // Verificar se a candidatura pertence a uma oportunidade da empresa logada
        $company = $request->user();
        $oportunidadeIds = $company->oportunidades()->pluck('id');
        
        if (!$oportunidadeIds->contains($candidatura->oportunidade_id)) {
            return response()->json([
                'message' => 'Acesso negado. Esta candidatura não pertence às suas oportunidades.'
            ], 403);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5'
        ]);

        $candidatura->update([
            'company_rating' => $validated['rating']
        ]);

        return response()->json([
            'message' => 'Avaliação atualizada com sucesso!',
            'candidatura' => $candidatura->fresh()
        ]);
    }

    /**
     * Extrai skills do CV analisando o conteúdo do PDF
     */
    private function extrairSkillsDoCV($arquivo, $skillsDesejadas)
    {
        try {
            // Lista de skills técnicas para buscar
            $skillsComuns = [
                'PHP', 'Laravel', 'MySQL', 'JavaScript', 'Vue.js', 'React', 'Angular',
                'HTML', 'CSS', 'Bootstrap', 'TailwindCSS', 'Git', 'Docker', 'Redis',
                'PostgreSQL', 'Node.js', 'Python', 'Java', 'C#', 'TypeScript'
            ];
            
            // Ler o conteúdo do PDF
            $conteudoCV = $this->extrairTextoDoCV($arquivo);
            
            $skillsEncontradas = [];
            
            // Buscar skills no conteúdo do CV (case insensitive)
            foreach ($skillsComuns as $skill) {
                if (stripos($conteudoCV, $skill) !== false) {
                    $skillsEncontradas[] = $skill;
                }
            }
            
            return array_unique($skillsEncontradas);
            
        } catch (Exception $e) {
            // Se der erro na extração, retornar apenas as skills desejadas
            return $skillsDesejadas;
        }
    }
    
    /**
     * Extrai texto do arquivo PDF
     */
    private function extrairTextoDoCV($arquivo)
    {
        try {
            // Salvar arquivo temporariamente
            $tempPath = sys_get_temp_dir() . '/' . uniqid() . '.pdf';
            $arquivo->move(dirname($tempPath), basename($tempPath));
            
            // Usar pdftotext para extrair texto
            $comando = "pdftotext '{$tempPath}' -";
            $output = shell_exec($comando);
            
            // Remover arquivo temporário
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
            
            return $output ?: '';
            
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Extração básica de skills - versão simplificada (método legado)
     * Na versão real, isto seria feito com análise do PDF
     */
    private function extrairSkillsBasicas($skillsDesejadas)
    {
        // Por simplicidade, vamos simular que algumas skills foram encontradas
        // Em produção, aqui seria implementada a análise do CV
        $skillsComuns = ['PHP', 'JavaScript', 'HTML', 'CSS', 'Laravel', 'Vue.js', 'MySQL', 'Git'];
        
        // Simular que encontramos algumas skills aleatórias
        $skillsEncontradas = [];
        foreach ($skillsDesejadas as $skill) {
            // 70% de chance de "encontrar" a skill no CV
            if (rand(1, 10) <= 7) {
                $skillsEncontradas[] = $skill;
            }
        }
        
        // Adicionar algumas skills comuns aleatórias
        $skillsAdicionais = array_diff($skillsComuns, $skillsEncontradas);
        $numAdicionais = rand(1, 3);
        for ($i = 0; $i < $numAdicionais && count($skillsAdicionais) > 0; $i++) {
            $skillAleatoria = array_rand($skillsAdicionais);
            $skillsEncontradas[] = $skillsAdicionais[$skillAleatoria];
            unset($skillsAdicionais[$skillAleatoria]);
        }
        
        return array_unique($skillsEncontradas);
    }

    /**
     * Adiciona o candidato ao grupo de audiência do Resend para marketing
     * 
     * @param Candidatura $candidatura
     * @return void
     */
    private function adicionarCandidatoAoGrupoDeAudiencia(Candidatura $candidatura)
    {
        try {
            Log::info("Iniciando processo de adição do candidato ao Resend", [
                'candidato_email' => $candidatura->email,
                'oportunidade_id' => $candidatura->oportunidade_id,
                'rgpd_aceito' => $candidatura->rgpd_aceito
            ]);

            // Verificar se o candidato aceitou o RGPD antes de adicionar
            if (!$candidatura->rgpd_aceito) {
                Log::info("Candidato {$candidatura->email} não aceitou o RGPD, não será adicionado ao grupo de audiência");
                return;
            }

            // Verificar se o Resend está habilitado
            if (!config('resend.enabled', false)) {
                Log::info("Resend está desabilitado na configuração. Tentando forçar habilitação...");
                // Forçar habilitação do Resend para este fluxo
                $this->resendService->forcarHabilitar(true);
            }

            // Verificar se a API key está configurada
            if (empty(config('resend.api_key'))) {
                Log::warning("API key do Resend não está configurada");
                return;
            }

            Log::info("Tentando obter/criar grupo de audiência para oportunidade {$candidatura->oportunidade_id}");

            // Obter ou criar o grupo de audiência para esta oportunidade
            $grupoId = $this->resendService->obterOuCriarGrupoAudiencia($candidatura->oportunidade_id);
            
            Log::info("Resultado da obtenção/criação do grupo", [
                'grupo_id' => $grupoId,
                'oportunidade_id' => $candidatura->oportunidade_id
            ]);
            
            if ($grupoId) {
                // Separar nome e sobrenome para seguir o formato da documentação
                $nomeParts = explode(' ', $candidatura->nome, 2);
                $firstName = $nomeParts[0] ?? $candidatura->nome;
                $lastName = $nomeParts[1] ?? '';
                
                Log::info("Tentando adicionar contato ao grupo", [
                    'grupo_id' => $grupoId,
                    'email' => $candidatura->email,
                    'first_name' => $firstName,
                    'last_name' => $lastName
                ]);
                
                // Adicionar o candidato ao grupo
                $resultado = $this->resendService->adicionarContatoAoGrupo(
                    $grupoId,
                    $candidatura->email,
                    $firstName,
                    $lastName
                );
                
                if ($resultado) {
                    Log::info("Candidato {$candidatura->email} adicionado ao grupo de audiência {$grupoId} com sucesso");
                } else {
                    Log::warning("Falha ao adicionar candidato {$candidatura->email} ao grupo {$grupoId}");
                }
            } else {
                Log::warning("Não foi possível obter/criar grupo de audiência para oportunidade {$candidatura->oportunidade_id}");
            }
        } catch (Exception $e) {
            // Apenas log do erro, não interrompe o fluxo principal
            Log::error('Erro ao adicionar candidato ao grupo de audiência: ' . $e->getMessage(), [
                'candidato_email' => $candidatura->email ?? 'N/A',
                'oportunidade_id' => $candidatura->oportunidade_id ?? 'N/A',
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
