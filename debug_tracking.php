<?php

/**
 * Script para debugar o sistema de tracking
 * Verifica se visitas estão sendo registradas e diagnostica problemas
 */

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PageVisit;
use App\Models\Oportunidade;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

// 1. Verificar se existem oportunidades
$oportunidades = Oportunidade::all();
echo "1. Oportunidades cadastradas: " . $oportunidades->count() . "\n";
if ($oportunidades->count() === 0) {
    echo "   ❌ Não há oportunidades cadastradas para rastrear visitas!\n";
} else {
    echo "   ✅ Existem oportunidades cadastradas\n";
    $oportunidades->each(function($o) {
        echo "      - {$o->titulo} (slug: {$o->slug})\n";
    });
}

// 2. Verificar se há visitas registradas
$visitas = PageVisit::all();
echo "\n2. Visitas registradas: " . $visitas->count() . "\n";
if ($visitas->count() === 0) {
    echo "   ⚠️ Nenhuma visita registrada ainda\n";
} else {
    echo "   ✅ Existem visitas registradas\n";
    // Mostrar as últimas 5 visitas
    $visitas->take(5)->each(function($v) {
        $oportunidade = Oportunidade::find($v->oportunidade_id);
        $titulo = $oportunidade ? $oportunidade->titulo : 'Desconhecida';
        echo "      - {$v->visited_at} | IP: {$v->ip_address} | Oportunidade: {$titulo}\n";
    });
}

// 3. Criar uma visita forçada (para teste)
echo "\n3. Criando uma visita de teste\n";
try {
    if ($oportunidades->count() > 0) {
        $oportunidade = $oportunidades->first();
        
        $visit = new PageVisit();
        $visit->oportunidade_id = $oportunidade->id;
        $visit->ip_address = '127.0.0.1';
        $visit->user_agent = 'Debug Script';
        $visit->browser = 'Debug Browser';
        $visit->platform = 'Test Platform';
        $visit->country = 'Brasil';
        $visit->city = 'São Paulo';
        $visit->region = 'SP';
        $visit->latitude = -23.5505;
        $visit->longitude = -46.6333;
        $visit->visited_at = Carbon::now();
        $visit->save();
        
        echo "   ✅ Visita de teste criada com sucesso!\n";
    } else {
        echo "   ❌ Não foi possível criar visita de teste (sem oportunidades)\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Erro ao criar visita de teste: " . $e->getMessage() . "\n";
}

// 4. Verificar se o banco de dados GeoIP está configurado
echo "\n4. Verificando configuração de GeoIP\n";
$geoipPath = storage_path('geoip');
$mmdbFile = $geoipPath . '/GeoLite2-City.mmdb';
$tarFile = $geoipPath . '/GeoLite2-City.tar.gz';

echo "   - Diretório GeoIP: " . (is_dir($geoipPath) ? "✅ Existe" : "❌ Não existe") . "\n";
echo "   - Arquivo .mmdb: " . (file_exists($mmdbFile) ? "✅ Existe" : "❌ Não existe") . "\n";
echo "   - Arquivo .tar.gz: " . (file_exists($tarFile) ? "✅ Existe (" . filesize($tarFile) . " bytes)" : "❌ Não existe") . "\n";

if (file_exists($tarFile) && filesize($tarFile) === 0) {
    echo "   ⚠️ O arquivo GeoLite2-City.tar.gz está vazio (0 bytes)!\n";
    echo "   ℹ️ Isso pode explicar porque o tracking não está funcionando corretamente.\n";
}

// 5. Contornar problema do GeoIP
echo "\n5. Modificando o GeoLocationService para contornar problema de GeoIP\n";

try {
    // Criar um arquivo temporário com uma classe modificada
    $tempFile = __DIR__ . '/app/Services/GeoLocationServicePatched.php';
    $content = file_get_contents(__DIR__ . '/app/Services/GeoLocationService.php');
    
    // Modificar a implementação do método getLocationData para sempre retornar dados simulados
    $patched = preg_replace(
        '/public function getLocationData\(\$ipAddress\)\s*{.*?}/s', 
        'public function getLocationData($ipAddress)
    {
        // Sempre retorna dados simulados para contornar problema do GeoIP
        return [
            \'country\' => \'Brasil\',
            \'city\' => \'São Paulo\',
            \'region\' => \'São Paulo\',
            \'latitude\' => -23.5505,
            \'longitude\' => -46.6333
        ];
    }', 
        $content
    );
    
    file_put_contents($tempFile, $patched);
    echo "   ✅ Patch criado em: {$tempFile}\n";
    echo "   ℹ️ Use este arquivo modificado temporariamente para contornar o problema de GeoIP\n";
} catch (\Exception $e) {
    echo "   ❌ Erro ao criar patch: " . $e->getMessage() . "\n";
}

echo "\nDiagnóstico concluído. Se você acessar a página pública de oportunidade agora,\n";
echo "uma visita simulada já foi criada e deve aparecer nos relatórios.\n";
echo "Para atualizar, acesse a interface administrativa e verifique os relatórios novamente.\n";
