<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Candidato;
use App\Models\Candidatura;
use Illuminate\Support\Facades\DB;

class EncryptExistingData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'gdpr:encrypt-existing-data 
                            {--model= : Modelo específico para processar (candidatos|candidaturas|all)}
                            {--dry-run : Executar sem fazer alterações}
                            {--force : Forçar criptografia mesmo se dados parecem já estar criptografados}';

    /**
     * The console command description.
     */
    protected $description = 'Criptografa dados pessoais existentes para compliance GDPR';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $model = $this->option('model') ?? 'all';
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 Modo de simulação ativado - nenhuma alteração será feita');
        }

        $this->info('🔐 Iniciando processo de criptografia de dados pessoais...');

        if ($model === 'all' || $model === 'candidatos') {
            $this->encryptCandidatos($dryRun);
        }

        if ($model === 'all' || $model === 'candidaturas') {
            $this->encryptCandidaturas($dryRun);
        }

        $this->info('✅ Processo de criptografia concluído!');
    }

    /**
     * Criptografa dados de candidatos
     */
    private function encryptCandidatos($dryRun = false)
    {
        $this->info('📋 Processando candidatos...');
        
        $candidatos = Candidato::all();
        $processedCount = 0;
        
        $bar = $this->output->createProgressBar($candidatos->count());
        $bar->start();

        foreach ($candidatos as $candidato) {
            if (!$dryRun) {
                DB::transaction(function () use ($candidato, &$processedCount) {
                    if ($candidato->encryptExistingData()) {
                        $processedCount++;
                    }
                });
            } else {
                // No modo dry-run, apenas simula
                $hasUnencryptedData = $this->hasUnencryptedData($candidato);
                if ($hasUnencryptedData) {
                    $processedCount++;
                }
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        
        $action = $dryRun ? 'seriam processados' : 'foram criptografados';
        $this->info("📊 {$processedCount} candidatos {$action}");
    }

    /**
     * Criptografa dados de candidaturas
     */
    private function encryptCandidaturas($dryRun = false)
    {
        $this->info('📄 Processando candidaturas...');
        
        $candidaturas = Candidatura::all();
        $processedCount = 0;
        
        $bar = $this->output->createProgressBar($candidaturas->count());
        $bar->start();

        foreach ($candidaturas as $candidatura) {
            if (!$dryRun) {
                DB::transaction(function () use ($candidatura, &$processedCount) {
                    if ($candidatura->encryptExistingData()) {
                        $processedCount++;
                    }
                });
            } else {
                // No modo dry-run, apenas simula
                $hasUnencryptedData = $this->hasUnencryptedData($candidatura);
                if ($hasUnencryptedData) {
                    $processedCount++;
                }
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        
        $action = $dryRun ? 'seriam processadas' : 'foram criptografadas';
        $this->info("📊 {$processedCount} candidaturas {$action}");
    }

    /**
     * Verifica se o modelo tem dados não criptografados
     */
    private function hasUnencryptedData($model)
    {
        $reflection = new \ReflectionClass($model);
        if (!$reflection->hasMethod('getEncryptableFieldsList')) {
            return false;
        }

        foreach ($model->getEncryptableFieldsList() as $field) {
            $value = $model->getOriginal($field); // Usa getOriginal em vez de getAttributeFromArray
            if (!empty($value) && !$model->isValueEncrypted($value)) {
                return true; // Tem dados não criptografados
            }
        }
        
        return false;
    }
}
