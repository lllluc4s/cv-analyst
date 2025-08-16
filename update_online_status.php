<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Candidato;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "Atualizando candidatos para aparecer como online...\n\n";
    
    // Atualizar todos os candidatos para estar online agora
    $candidatos = Candidato::where('is_online', true)->get();
    
    foreach ($candidatos as $candidato) {
        $candidato->update([
            'last_seen_at' => now(), // Marca como visto agora
            'is_online' => true
        ]);
        
        echo "✓ Candidato {$candidato->nome} marcado como online agora\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "ATUALIZAÇÃO CONCLUÍDA!\n";
    echo "Todos os candidatos agora aparecem como online.\n";
    echo str_repeat("=", 50) . "\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
