<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\GeoLocationService;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TrackCandidatoActivity
{
    /**
     * Serviço de GeoLocalização
     */
    protected $geoService;
    
    public function __construct(GeoLocationService $geoService)
    {
        $this->geoService = $geoService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Só rastrear candidatos autenticados
        if ($request->user() && $request->user() instanceof \App\Models\Candidato) {
            $this->trackActivity($request);
        }

        return $response;
    }

    /**
     * Rastreia a atividade do candidato
     */
    private function trackActivity(Request $request)
    {
        try {
            $candidato = $request->user();
            $ipAddress = $request->ip();

            // Obter dados de geolocalização
            $locationData = $this->geoService->getLocationData($ipAddress);

            // Atualizar a última atividade do candidato
            $candidato->markOnline(
                $locationData['latitude'] ?? null,
                $locationData['longitude'] ?? null,
                $locationData['country'] ?? null,
                $locationData['city'] ?? null,
                $locationData['region'] ?? null
            );

            Log::info("Candidato {$candidato->id} marcado como online", [
                'location' => $locationData,
                'ip' => $ipAddress
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao rastrear atividade do candidato: ' . $e->getMessage());
        }
    }
}
