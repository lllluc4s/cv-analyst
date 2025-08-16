<?php

// Este script adiciona headers adicionais para diagnosticar problemas no frontend
header('Access-Control-Allow-Origin: *');
header('Content-Type: text/plain');

// Carregar a aplicação
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Oportunidade;
use App\Models\PageVisit;
use App\Services\GeoLocationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

echo "Diagnóstico de tracking de visitas - " . date('Y-m-d H:i:s') . "\n";
echo "=========================================================\n\n";

// 1. Verificar a configuração das rotas
echo "1. Verificando rotas e middleware:\n";
$routes = Route::getRoutes();
$targetRoute = null;
foreach ($routes as $route) {
    if ($route->getName() === 'oportunidade.public') {
        $targetRoute = $route;
        break;
    }
}

if ($targetRoute) {
    echo "  ✓ Rota 'oportunidade.public' encontrada: " . $targetRoute->uri() . "\n";
    $middlewares = $targetRoute->gatherMiddleware();
    echo "  ✓ Middlewares: " . implode(', ', $middlewares) . "\n";
    
    if (in_array('track.visit', $middlewares)) {
        echo "  ✓ Middleware 'track.visit' está configurado na rota\n";
    } else {
        echo "  ✗ Middleware 'track.visit' NÃO está na rota!\n";
    }
} else {
    echo "  ✗ Rota 'oportunidade.public' não encontrada!\n";
}

// 2. Verificar se temos oportunidades ativas
echo "\n2. Verificando oportunidades disponíveis:\n";
$oportunidades = Oportunidade::where('ativa', true)->get();
echo "  ✓ Total de oportunidades ativas: " . $oportunidades->count() . "\n";

if ($oportunidades->isNotEmpty()) {
    $oportunidade = $oportunidades->first();
    echo "  ✓ Primeira oportunidade: ID #{$oportunidade->id} - {$oportunidade->titulo} (slug: {$oportunidade->slug})\n";
    echo "  ✓ URL da oportunidade: " . url("/oportunidade/{$oportunidade->slug}") . "\n";
} else {
    echo "  ✗ Nenhuma oportunidade ativa encontrada!\n";
}

// 3. Verificar serviço de geolocalização
echo "\n3. Verificando serviço de geolocalização:\n";
$geoService = new GeoLocationService();
$testIP = "8.8.8.8";
$locationData = $geoService->getLocationData($testIP);

if ($locationData && isset($locationData['country'])) {
    echo "  ✓ Geolocalização funcionando para IP de teste ({$testIP}):\n";
    echo "    - País: " . ($locationData['country'] ?? 'N/A') . "\n";
    echo "    - Cidade: " . ($locationData['city'] ?? 'N/A') . "\n";
    echo "    - Região: " . ($locationData['region'] ?? 'N/A') . "\n";
    echo "    - Coordenadas: " . ($locationData['latitude'] ?? 'N/A') . ", " . ($locationData['longitude'] ?? 'N/A') . "\n";
} else {
    echo "  ✗ Falha no serviço de geolocalização!\n";
}

// 4. Verificar banco de dados e tabelas
echo "\n4. Verificando estrutura do banco de dados:\n";
try {
    // Verificar se a tabela existe
    $tableExists = DB::getSchemaBuilder()->hasTable('page_visits');
    if ($tableExists) {
        echo "  ✓ Tabela 'page_visits' existe\n";
        
        // Verificar as colunas
        $columns = DB::select('PRAGMA table_info(page_visits)');
        $columnNames = array_map(function($col) { return $col->name; }, $columns);
        echo "  ✓ Colunas encontradas: " . implode(', ', $columnNames) . "\n";
        
        // Verificar se há registros
        $visitsCount = PageVisit::count();
        echo "  ✓ Total de visitas registradas: " . $visitsCount . "\n";
        
    } else {
        echo "  ✗ Tabela 'page_visits' não existe!\n";
    }
} catch (\Exception $e) {
    echo "  ✗ Erro ao verificar banco de dados: " . $e->getMessage() . "\n";
}

// 5. Verificar permissões de escrita no banco
echo "\n5. Verificando permissões de escrita no banco de dados:\n";
try {
    DB::beginTransaction();
    
    // Tentar criar um registro de teste
    $testVisit = new PageVisit();
    $testVisit->oportunidade_id = $oportunidades->first()->id ?? 1;
    $testVisit->ip_address = '127.0.0.1';
    $testVisit->user_agent = 'Diagnóstico/1.0';
    $testVisit->visited_at = now();
    $testVisit->save();
    
    echo "  ✓ Permissões de escrita OK (ID do registro de teste: {$testVisit->id})\n";
    
    // Remover o registro de teste
    $testVisit->delete();
    
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    echo "  ✗ Erro ao escrever no banco: " . $e->getMessage() . "\n";
}

// 6. Diagnóstico final
echo "\n6. Diagnóstico final:\n";

// Verificar se já houve algum registro nas últimas horas
$recentVisits = PageVisit::where('created_at', '>=', now()->subHours(2))
                         ->where('ip_address', '!=', '127.0.0.1')
                         ->orderBy('created_at', 'desc')
                         ->limit(5)
                         ->get();

if ($recentVisits->isNotEmpty()) {
    echo "  ✓ Visitas recentes encontradas:\n";
    foreach ($recentVisits as $v) {
        echo "    - {$v->created_at}: IP {$v->ip_address} em {$v->country}/{$v->city}\n";
    }
    echo "\n  ✅ O sistema de tracking parece estar funcionando corretamente.\n";
    echo "     Quando você acessa a página, verifique se está usando a URL exata:\n";
    echo "     " . url("/oportunidade/{$oportunidades->first()->slug}") . "\n";
} else {
    echo "  ⚠️ Não foram encontradas visitas recentes não-locais.\n";
    echo "     Isso pode indicar um problema com o tracking em produção.\n";
    echo "     Recomendação: Verifique se está acessando a URL correta e se o\n";
    echo "     servidor web está passando os headers e endereços IP corretamente.\n";
}

// Instruções adicionais para diagnóstico
echo "\n7. Instruções para testes adicionais:\n";
echo "  - Acesse a URL: " . url("/oportunidade/{$oportunidades->first()->slug}") . "\n";
echo "  - Após acessar, execute: php verify_visits.php\n";
echo "  - Verifique os logs: tail -f storage/logs/laravel.log\n";
echo "  - Limpe o cache do Laravel: php artisan optimize:clear\n";

// Verificar se há algum erro de proxy ou configuração de IP
echo "\nIP da requisição atual (para referência): " . request()->ip() . "\n";

if (strpos(request()->ip(), '127.0.0.1') === 0 || strpos(request()->ip(), '192.168') === 0) {
    echo "\n⚠️ NOTA: Seu IP atual é local (" . request()->ip() . ").\n";
    echo "         Se estiver acessando de um navegador externo, há um problema\n";
    echo "         de configuração no servidor web que está mascarando o IP real.\n";
    echo "         Verifique as configurações do servidor para trust proxies.\n";
}

echo "\nDiagnóstico concluído.\n";
