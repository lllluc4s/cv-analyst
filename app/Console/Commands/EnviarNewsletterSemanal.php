<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Candidatura;
use App\Models\Oportunidade;
use App\Services\ResendService;
use App\Mail\NewsletterSemanal;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EnviarNewsletterSemanal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'newsletter:enviar-semanal {--teste : Executar em modo de teste}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia newsletter semanal com as últimas 20 oportunidades para todos os candidatos registados';

    protected $resendService;

    public function __construct(ResendService $resendService)
    {
        parent::__construct();
        $this->resendService = $resendService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isTeste = $this->option('teste');
        
        $this->info('Iniciando envio da newsletter semanal...');
        
        try {
            // Obter as 20 oportunidades mais recentes que estão ativas e públicas
            $oportunidades = Oportunidade::with('company')
                ->where('ativa', true)
                ->where('publica', true)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            if ($oportunidades->isEmpty()) {
                $this->warn('Nenhuma oportunidade ativa encontrada para enviar na newsletter.');
                return 0;
            }

            $this->info("Encontradas {$oportunidades->count()} oportunidades para enviar.");

            // Obter todos os candidatos únicos que aceitaram RGPD
            $candidatos = Candidatura::where('rgpd_aceito', true)
                ->whereNotNull('email')
                ->select('email', 'nome')
                ->distinct('email')
                ->get();

            if ($candidatos->isEmpty()) {
                $this->warn('Nenhum candidato encontrado que aceitou receber comunicações.');
                return 0;
            }

            $this->info("Encontrados {$candidatos->count()} candidatos para enviar newsletter.");

            // Em modo de teste, enviar apenas para alguns emails específicos
            if ($isTeste) {
                $candidatos = collect([
                    (object)['email' => 'lucas.rodrigues@team.inovcorp.com', 'nome' => 'Lucas Rodrigues (Teste)'],
                    (object)['email' => 'teste@example.com', 'nome' => 'Usuário Teste']
                ]);
                $this->info("Modo de teste ativado - enviando apenas para emails de teste.");
            }

            $sucessos = 0;
            $falhas = 0;

            // Preparar dados para o template
            $dadosNewsletter = [
                'oportunidades' => $oportunidades,
                'data_envio' => Carbon::now()->format('d/m/Y'),
                'total_oportunidades' => $oportunidades->count()
            ];

            // Enviar newsletter para cada candidato
            foreach ($candidatos as $candidato) {
                try {
                    $this->line("Enviando para: {$candidato->email}");
                    
                    Mail::to($candidato->email)->send(new NewsletterSemanal($dadosNewsletter, $candidato->nome));
                    $sucessos++;
                    
                } catch (\Exception $e) {
                    $this->error("Erro ao enviar para {$candidato->email}: " . $e->getMessage());
                    Log::error("Erro no envio da newsletter", [
                        'email' => $candidato->email,
                        'erro' => $e->getMessage()
                    ]);
                    $falhas++;
                }
                
                // Pequena pausa para não sobrecarregar o serviço de email
                sleep(1);
            }

            // Relatório final
            $this->info("\n=== RELATÓRIO DE ENVIO DA NEWSLETTER ===");
            $this->info("Total de oportunidades incluídas: {$oportunidades->count()}");
            $this->info("Total de candidatos: {$candidatos->count()}");
            $this->info("Sucessos: {$sucessos}");
            $this->info("Falhas: {$falhas}");
            
            if ($isTeste) {
                $this->warn("ATENÇÃO: Este foi um envio de teste!");
            }

            Log::info("Newsletter semanal enviada", [
                'total_oportunidades' => $oportunidades->count(),
                'total_candidatos' => $candidatos->count(),
                'sucessos' => $sucessos,
                'falhas' => $falhas,
                'modo_teste' => $isTeste
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error("Erro geral no envio da newsletter: " . $e->getMessage());
            Log::error("Erro geral na newsletter semanal", [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
