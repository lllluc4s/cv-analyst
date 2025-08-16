<?php

/**
 * Script de demonstração do Kanban Board
 * Execute: php teste_kanban.php
 */

require_once 'vendor/autoload.php';

use App\Models\Company;
use App\Models\Oportunidade;
use App\Models\Candidatura;
use App\Models\BoardState;

// Configurar aplicação Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TESTE DO KANBAN BOARD ===\n\n";

try {
    // 1. Buscar empresa de teste
    $company = Company::first();
    if (!$company) {
        echo "❌ Nenhuma empresa encontrada. Execute os seeders primeiro.\n";
        exit(1);
    }
    
    echo "✅ Empresa encontrada: {$company->name}\n";
    
    // 2. Buscar oportunidade da empresa
    $oportunidade = $company->oportunidades()->first();
    if (!$oportunidade) {
        echo "❌ Nenhuma oportunidade encontrada para a empresa.\n";
        exit(1);
    }
    
    echo "✅ Oportunidade encontrada: {$oportunidade->titulo}\n";
    
    // 3. Verificar estados padrão
    $states = BoardState::default()->ordered()->get();
    echo "✅ Estados padrão configurados: " . $states->count() . "\n";
    foreach ($states as $state) {
        echo "   - {$state->nome} ({$state->cor})\n";
    }
    
    // 4. Buscar candidaturas da oportunidade
    $candidaturas = Candidatura::where('oportunidade_id', $oportunidade->id)->get();
    echo "✅ Candidaturas encontradas: " . $candidaturas->count() . "\n";
    
    // 5. Simular organização no board
    echo "\n=== BOARD ATUAL ===\n";
    foreach ($states as $state) {
        $candidaturasDoEstado = $candidaturas->filter(function($c) use ($state) {
            return $c->board_state_id == $state->id || 
                   ($c->board_state_id === null && $state->ordem === 1);
        });
        
        echo "\n📋 {$state->nome} ({$candidaturasDoEstado->count()} candidatos)\n";
        foreach ($candidaturasDoEstado as $candidatura) {
            echo "   👤 {$candidatura->nome} - {$candidatura->email}\n";
        }
    }
    
    // 6. Simular movimentação de candidato
    $primeiraCandidatura = $candidaturas->first();
    if ($primeiraCandidatura) {
        $estadoEntrevista = $states->where('nome', 'Entrevista')->first();
        if ($estadoEntrevista) {
            $primeiraCandidatura->update([
                'board_state_id' => $estadoEntrevista->id,
                'moved_to_state_at' => now()
            ]);
            
            echo "\n✅ Candidato {$primeiraCandidatura->nome} movido para 'Entrevista'\n";
        }
    }
    
    // 7. Verificar se candidaturas novas são automaticamente colocadas no primeiro estado
    echo "\n=== TESTE DE AUTO-ATRIBUIÇÃO DE ESTADO ===\n";
    $novaCandidatura = new Candidatura([
        'oportunidade_id' => $oportunidade->id,
        'nome' => 'Teste Kanban',
        'apelido' => 'Teste',
        'email' => 'teste.kanban@email.com',
        'telefone' => '11999999999',
        'cv_path' => 'test.pdf',
        'rgpd_aceito' => true,
        'slug' => 'teste-kanban-' . uniqid()
    ]);
    
    $novaCandidatura->save();
    echo "✅ Nova candidatura criada automaticamente no estado: {$novaCandidatura->boardState->nome}\n";
    
    // Limpar dados de teste
    $novaCandidatura->delete();
    echo "✅ Dados de teste limpos\n";
    
    echo "\n🎉 Teste do Kanban Board concluído com sucesso!\n";
    echo "\n📖 Consulte KANBAN_BOARD_DOCS.md para documentação completa da API.\n";
    
} catch (Exception $e) {
    echo "❌ Erro durante o teste: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
