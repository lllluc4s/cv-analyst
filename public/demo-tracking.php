<?php
header('Content-Type: text/html; charset=utf-8');

// Carregar a aplicação
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PageVisit;
use App\Models\Oportunidade;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

// Obter estatísticas
$totalVisitas = PageVisit::count();
$visitasHoje = PageVisit::whereDate('created_at', Carbon::today())->count();
$visitasOntem = PageVisit::whereDate('created_at', Carbon::yesterday())->count();
$visitasUltimos7Dias = PageVisit::where('created_at', '>=', Carbon::now()->subDays(7))->count();

// Obter visitas por navegador
$navegadores = PageVisit::selectRaw('browser, count(*) as total')
    ->whereNotNull('browser')
    ->where('browser', '!=', '')
    ->where('browser', '!=', '0')
    ->groupBy('browser')
    ->orderByDesc('total')
    ->get();

// Obter visitas por país
$paises = PageVisit::selectRaw('country, count(*) as total')
    ->whereNotNull('country')
    ->groupBy('country')
    ->orderByDesc('total')
    ->get();

// Obter últimas visitas
$ultimasVisitas = PageVisit::with('oportunidade')
    ->orderByDesc('created_at')
    ->limit(10)
    ->get();

// Obter dados para o mapa de calor
$localizacoes = PageVisit::selectRaw('latitude, longitude, count(*) as total')
    ->whereNotNull('latitude')
    ->whereNotNull('longitude')
    ->where('latitude', '!=', 0)
    ->where('longitude', '!=', 0)
    ->groupBy('latitude', 'longitude')
    ->get();

// Visitas por dia nos últimos 14 dias
$visitasPorDia = [];
for ($i = 13; $i >= 0; $i--) {
    $data = Carbon::now()->subDays($i)->format('Y-m-d');
    $visitasPorDia[$data] = 0;
}

$visitasDias = PageVisit::selectRaw('DATE(created_at) as data, count(*) as total')
    ->where('created_at', '>=', Carbon::now()->subDays(14))
    ->groupBy('data')
    ->get();

foreach ($visitasDias as $visita) {
    if (isset($visitasPorDia[$visita->data])) {
        $visitasPorDia[$visita->data] = $visita->total;
    }
}

// Formatar os dados para o gráfico
$datasGrafico = array_keys($visitasPorDia);
$valoresGrafico = array_values($visitasPorDia);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Tracking - Demonstração</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <style>
        body { padding-top: 20px; }
        .card { margin-bottom: 20px; }
        .stat-card { text-align: center; padding: 15px; }
        .stat-card h2 { font-size: 32px; font-weight: bold; margin-bottom: 5px; }
        .stat-card p { font-size: 14px; color: #666; margin-bottom: 0; }
        #visitMap { height: 400px; }
        .table th { position: sticky; top: 0; background: white; }
        .date-display { font-size: 14px; color: #666; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Dashboard de Tracking e Geolocalização</h1>
        <p class="date-display">Demonstração gerada em: <?php echo Carbon::now()->format('d/m/Y H:i:s'); ?></p>
        
        <div class="row">
            <div class="col-md-3">
                <div class="card stat-card">
                    <h2><?php echo $totalVisitas; ?></h2>
                    <p>Total de visitas</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <h2><?php echo $visitasHoje; ?></h2>
                    <p>Visitas hoje</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <h2><?php echo $visitasOntem; ?></h2>
                    <p>Visitas ontem</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <h2><?php echo $visitasUltimos7Dias; ?></h2>
                    <p>Últimos 7 dias</p>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Visitas por dia (Últimos 14 dias)</div>
                    <div class="card-body">
                        <canvas id="visitsChart" height="250"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Top Navegadores</div>
                    <div class="card-body">
                        <?php if ($navegadores->count()): ?>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Navegador</th>
                                        <th>Visitas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($navegadores as $nav): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($nav->browser); ?></td>
                                        <td><?php echo $nav->total; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-muted">Nenhum dado disponível</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">Top Países</div>
                    <div class="card-body">
                        <?php if ($paises->count()): ?>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>País</th>
                                        <th>Visitas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($paises as $pais): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($pais->country); ?></td>
                                        <td><?php echo $pais->total; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-muted">Nenhum dado disponível</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Mapa de Visitas</div>
                    <div class="card-body">
                        <div id="visitMap"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Últimas Visitas Registradas</div>
                    <div class="card-body">
                        <?php if ($ultimasVisitas->count()): ?>
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-sm table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Data/Hora</th>
                                            <th>IP</th>
                                            <th>Navegador</th>
                                            <th>Plataforma</th>
                                            <th>Localização</th>
                                            <th>Oportunidade</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($ultimasVisitas as $visita): ?>
                                        <tr>
                                            <td><?php echo $visita->created_at->format('d/m/Y H:i:s'); ?></td>
                                            <td><?php echo htmlspecialchars($visita->ip_address); ?></td>
                                            <td><?php echo htmlspecialchars($visita->browser); ?></td>
                                            <td><?php echo htmlspecialchars($visita->platform); ?></td>
                                            <td>
                                                <?php 
                                                $location = [];
                                                if (!empty($visita->city)) $location[] = $visita->city;
                                                if (!empty($visita->region)) $location[] = $visita->region;
                                                if (!empty($visita->country)) $location[] = $visita->country;
                                                echo htmlspecialchars(implode(', ', $location));
                                                ?>
                                                <?php if ($visita->latitude && $visita->longitude): ?>
                                                <small class="text-muted d-block">
                                                    <?php echo $visita->latitude; ?>, <?php echo $visita->longitude; ?>
                                                </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($visita->oportunidade): ?>
                                                    <?php echo htmlspecialchars($visita->oportunidade->titulo); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Nenhuma visita registrada ainda</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <footer class="mt-5 mb-4 text-center text-muted">
            <!-- Rodapé limpo, sem instruções de teste -->
        </footer>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        // Configuração do gráfico de visitas
        const ctx = document.getElementById('visitsChart').getContext('2d');
        const visitsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_map(function($date) {
                    return Carbon::parse($date)->format('d/m');
                }, $datasGrafico)); ?>,
                datasets: [{
                    label: 'Visitas por dia',
                    data: <?php echo json_encode($valoresGrafico); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        
        // Configuração do mapa de visitas
        const map = L.map('visitMap').setView([0, 0], 2);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Adicionar marcadores para cada localização
        const locations = <?php echo json_encode($localizacoes); ?>;
        
        if (locations.length > 0) {
            locations.forEach(loc => {
                if (loc.latitude && loc.longitude) {
                    L.marker([loc.latitude, loc.longitude])
                        .addTo(map)
                        .bindPopup(`<b>${loc.total} visita(s)</b>`);
                }
            });
            
            // Centralizar o mapa na primeira localização (se existir)
            if (locations[0].latitude && locations[0].longitude) {
                map.setView([locations[0].latitude, locations[0].longitude], 4);
            }
        } else {
            document.getElementById('visitMap').innerHTML = '<div class="alert alert-info m-3">Nenhum dado de localização disponível</div>';
        }
    </script>
</body>
</html>
