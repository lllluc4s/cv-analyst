<?php

/**
 * Script para criar dados de teste para o Kanban Board
 */

require_once 'vendor/autoload.php';

use App\Models\Company;
use App\Models\Oportunidade;
use App\Models\Candidatura;
use App\Models\BoardState;

// Configurar aplicação Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CRIANDO DADOS DE TESTE PARA KANBAN BOARD ===\n\n";

try {
    // 1. Buscar empresa
    $company = Company::first();
    if (!$company) {
        echo "❌ Nenhuma empresa encontrada. Execute os seeders primeiro.\n";
        exit(1);
    }
    
    echo "✅ Empresa encontrada: {$company->name}\n";
    
    // 2. Buscar oportunidade
    $oportunidade = $company->oportunidades()->first();
    if (!$oportunidade) {
        echo "❌ Nenhuma oportunidade encontrada para a empresa.\n";
        exit(1);
    }
    
    echo "✅ Oportunidade encontrada: {$oportunidade->titulo}\n";
    
    // 3. Buscar estados
    $states = BoardState::default()->ordered()->get();
    echo "✅ Estados encontrados: " . $states->count() . "\n";
    
    // 4. Criar candidaturas de teste se não existirem
    $candidaturasExistentes = Candidatura::where('oportunidade_id', $oportunidade->id)->count();
    
    if ($candidaturasExistentes < 5) {
        echo "📝 Criando candidaturas de teste...\n";
        
        $candidatosTest = [
            [
                'nome' => 'João Silva',
                'apelido' => 'João',
                'email' => 'joao.teste@email.com',
                'telefone' => '911111111',
                'state_name' => 'Recebido'
            ],
            [
                'nome' => 'Maria Santos',
                'apelido' => 'Maria',
                'email' => 'maria.teste@email.com',
                'telefone' => '922222222',
                'state_name' => 'Em análise'
            ],
            [
                'nome' => 'Pedro Costa',
                'apelido' => 'Pedro',
                'email' => 'pedro.teste@email.com',
                'telefone' => '933333333',
                'state_name' => 'Entrevista'
            ],
            [
                'nome' => 'Ana Oliveira',
                'apelido' => 'Ana',
                'email' => 'ana.teste@email.com',
                'telefone' => '944444444',
                'state_name' => 'Recebido'
            ],
            [
                'nome' => 'Carlos Pereira',
                'apelido' => 'Carlos',
                'email' => 'carlos.teste@email.com',
                'telefone' => '955555555',
                'state_name' => 'Contratado'
            ]
        ];
        
        foreach ($candidatosTest as $index => $candidatoData) {
            $state = $states->where('nome', $candidatoData['state_name'])->first();
            
            $candidatura = Candidatura::create([
                'oportunidade_id' => $oportunidade->id,
                'nome' => $candidatoData['nome'],
                'apelido' => $candidatoData['apelido'],
                'email' => $candidatoData['email'],
                'telefone' => $candidatoData['telefone'],
                'cv_path' => 'cvs/teste-cv-' . ($index + 1) . '.pdf',
                'rgpd_aceito' => true,
                'consentimento_rgpd' => true,
                'skills_extraidas' => ['PHP', 'Laravel', 'Vue.js'],
                'pontuacao_skills' => rand(60, 95),
                'slug' => \Illuminate\Support\Str::slug($candidatoData['nome'] . '-' . uniqid()),
                'board_state_id' => $state->id,
                'moved_to_state_at' => now()->subHours(rand(1, 48))
            ]);
            
            echo "   👤 {$candidatura->nome} -> {$candidatoData['state_name']}\n";
        }
        
        echo "✅ Candidaturas de teste criadas!\n";
    } else {
        echo "✅ Candidaturas já existem: {$candidaturasExistentes}\n";
    }
    
    // 5. Mostrar resumo do board
    echo "\n=== RESUMO DO BOARD ===\n";
    foreach ($states as $state) {
        $count = Candidatura::where('oportunidade_id', $oportunidade->id)
            ->where('board_state_id', $state->id)
            ->count();
        echo "📋 {$state->nome}: {$count} candidatos\n";
    }
    
    echo "\n🎉 Dados de teste criados com sucesso!\n";
    echo "📱 Acesse: http://localhost:5175/empresas/oportunidades/{$oportunidade->id}/kanban\n";
    echo "🔑 Faça login como empresa para visualizar o board.\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
