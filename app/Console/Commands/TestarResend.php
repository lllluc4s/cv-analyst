<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ResendService;
use Illuminate\Support\Facades\Log;

class TestarResend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resend:test {oportunidade_id=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa a integração com o Resend';

    protected $resendService;

    /**
     * Create a new command instance.
     */
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
        $oportunidadeId = $this->argument('oportunidade_id');
        
        $this->info("Testando integração Resend para oportunidade {$oportunidadeId}");
        
        // 1. Testar configuração
        $this->info("1. Verificando configuração...");
        $this->line("API Key: " . (config('resend.api_key') ? 'Configurada' : 'NÃO CONFIGURADA'));
        $this->line("Enabled: " . (config('resend.enabled') ? 'Sim' : 'Não'));
        $this->line("API URL: " . config('resend.api_url', 'https://api.resend.com/v1'));
        
        if (!config('resend.api_key')) {
            $this->error("API Key do Resend não está configurada!");
            return 1;
        }
        
        if (!config('resend.enabled')) {
            $this->error("Resend está desabilitado!");
            return 1;
        }
        
        // 2. Testar obtenção/criação de grupo
        $this->info("2. Testando obtenção/criação de grupo...");
        $grupoId = $this->resendService->obterOuCriarGrupoAudiencia($oportunidadeId);
        
        if ($grupoId) {
            $this->info("✓ Grupo obtido/criado com sucesso: {$grupoId}");
        } else {
            $this->error("✗ Falha ao obter/criar grupo");
            return 1;
        }
        
        // 3. Testar adição de contato
        $this->info("3. Testando adição de contato...");
        $emailTeste = 'teste-' . time() . '@example.com';
        $resultado = $this->resendService->adicionarContatoAoGrupo($grupoId, $emailTeste, 'Teste', 'Usuario');
        
        if ($resultado) {
            $this->info("✓ Contato adicionado com sucesso: {$emailTeste}");
        } else {
            $this->error("✗ Falha ao adicionar contato");
        }
        
        // 4. Verificar logs
        $this->info("4. Verificando logs...");
        $this->line("Verifique storage/logs/laravel.log para detalhes dos logs");
        
        $this->info("Teste concluído!");
        return 0;
    }
}
