<?php

namespace App\Services;

use App\Models\Oportunidade;
use App\Models\PageVisit;
use App\Models\SocialShare;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;

class AnalyticsService
{
    /**
     * Serviço de geolocalização
     */
    protected $geoLocationService;
    
    /**
     * Agente para detecção de navegador/dispositivo
     */
    protected $agent;

    /**
     * Construtor do serviço
     */
    public function __construct(GeoLocationService $geoLocationService)
    {
        $this->geoLocationService = $geoLocationService;
        $this->agent = new Agent();
    }

    /**
     * Rastreia uma visita na página de oportunidade
     *
     * @param Request $request Requisição atual
     * @param Oportunidade $oportunidade Oportunidade que está sendo visitada
     * @return PageVisit|null
     */
    public function trackVisit(Request $request, Oportunidade $oportunidade)
    {
        // Obter IP do visitante
        $ipAddress = $request->ip();
        Log::info("AnalyticsService: Rastreando visita do IP: " . $ipAddress . " para oportunidade ID: " . $oportunidade->id);
        
        // Processar User-Agent
        $userAgent = $request->header('User-Agent');
        $this->agent->setUserAgent($userAgent);
        
        // Obter browser e plataforma
        $browser = $this->agent->browser();
        $platform = $this->agent->platform();
        
        // Se o browser ou platform for uma string vazia, nula ou '0', usar "Desconhecido"
        $browser = (!empty($browser) && $browser !== '0') ? $browser : 'Desconhecido';
        $platform = (!empty($platform) && $platform !== '0') ? $platform : 'Desconhecido';
        
        // Para dispositivos móveis, adicionar "(Mobile)" ao nome do navegador
        if ($this->agent->isMobile()) {
            $browser .= ' (Mobile)';
        }
        
        Log::info("AnalyticsService: Browser: " . $browser . ", Platform: " . $platform);
        
        // Obter dados de geolocalização
        $locationData = $this->geoLocationService->getLocationData($ipAddress);
        
        Log::info("AnalyticsService: Dados de localização obtidos: " . json_encode($locationData));
        
        try {
            // Criar registro de visita
            $visit = PageVisit::create([
                'oportunidade_id' => $oportunidade->id,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'browser' => $browser,
                'platform' => $platform,
                'country' => $locationData['country'] ?? null,
                'city' => $locationData['city'] ?? null,
                'region' => $locationData['region'] ?? null,
                'latitude' => $locationData['latitude'] ?? null,
                'longitude' => $locationData['longitude'] ?? null,
                'visited_at' => Carbon::now(),
            ]);
            
            Log::info("AnalyticsService: Visita registrada com sucesso, ID: " . $visit->id);
            return $visit;
        } catch (\Exception $e) {
            Log::error("AnalyticsService: Erro ao salvar visita: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtém relatórios de analíticos para uma oportunidade
     *
     * @param Oportunidade $oportunidade
     * @param int $days Número de dias para analisar
     * @return array
     */
    public function getReports(Oportunidade $oportunidade, $days = 30)
    {
        // Data inicial para o relatório
        $startDate = Carbon::now()->subDays($days)->startOfDay();
        
        // Obter todas as visitas no período
        $visits = PageVisit::where('oportunidade_id', $oportunidade->id)
            ->where('visited_at', '>=', $startDate)
            ->get();
            
        $totalVisitas = $visits->count();
        
        // Agrupar visitas por dia (incluindo o dia atual)
        $visitasPorDia = [];
        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $visitasPorDia[] = [
                'data' => $date,
                'total' => 0
            ];
        }
        
        foreach ($visits as $visit) {
            $date = $visit->visited_at->format('Y-m-d');
            
            // Encontrar e incrementar o dia correspondente
            foreach ($visitasPorDia as &$dia) {
                if ($dia['data'] === $date) {
                    $dia['total']++;
                    break;
                }
            }
        }
        
        // Agrupar visitas por browser
        $browserCounts = $visits->groupBy('browser')
            ->map(function ($items) {
                return $items->count();
            })
            ->filter(function ($count, $browser) {
                return $browser != null && $browser != '' && $browser != '0';
            })
            ->sortDesc();
            
        $browsersMaisUsados = [];
        foreach ($browserCounts as $browser => $count) {
            $browsersMaisUsados[] = [
                'browser' => $browser,
                'total' => $count
            ];
        }
        
        // Agrupar visitas por localização (cidade/país)
        $locationCounts = $visits->filter(function ($visit) {
            return $visit->city != null && $visit->country != null;
        })->groupBy(function ($visit) {
            return $visit->city . ', ' . $visit->country;
        })->map(function ($items) {
            $first = $items->first();
            return [
                'city' => $first->city,
                'region' => $first->region,
                'country' => $first->country,
                'latitude' => $first->latitude,
                'longitude' => $first->longitude,
                'total' => $items->count()
            ];
        })->sortByDesc('total')->values();
        
        // Obter dados de partilhas sociais
        $socialShares = SocialShare::where('oportunidade_id', $oportunidade->id)
            ->where('shared_at', '>=', $startDate)
            ->get();
            
        $totalPartilhas = $socialShares->count();
        
        // Agrupar partilhas por plataforma
        $partilhasPorPlataforma = $socialShares->groupBy('platform')
            ->map(function ($items) {
                return [
                    'platform' => $items->first()->platform,
                    'total' => $items->count()
                ];
            })->values()->toArray();
        
        // Agrupar partilhas por dia
        $partilhasPorDia = [];
        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $partilhasPorDia[] = [
                'data' => $date,
                'total' => 0
            ];
        }
        
        foreach ($socialShares as $share) {
            $date = $share->shared_at->format('Y-m-d');
            
            // Encontrar e incrementar o dia correspondente
            foreach ($partilhasPorDia as &$dia) {
                if ($dia['data'] === $date) {
                    $dia['total']++;
                    break;
                }
            }
        }
        
        return [
            'total_visitas' => $totalVisitas,
            'visitas_por_dia' => $visitasPorDia,
            'browsers_mais_usados' => $browsersMaisUsados,
            'visitas_por_cidade' => $locationCounts,
            'total_partilhas' => $totalPartilhas,
            'partilhas_por_plataforma' => $partilhasPorPlataforma,
            'partilhas_por_dia' => $partilhasPorDia
        ];
    }
}
