<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Oportunidade;
use App\Services\AnalyticsService;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TrackPageVisit
{
    /**
     * Serviço de Analytics
     */
    protected $analyticsService;
    
    /**
     * Constructor
     */
    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Só rastrear se for uma página pública de oportunidade
        if ($request->route() && $request->route()->getName() === 'oportunidade.public') {
            $this->trackVisit($request);
        }

        return $response;
    }

    /**
     * Rastreia visita à página de oportunidade
     */
    private function trackVisit(Request $request)
    {
        // Obter a oportunidade da rota
        $slug = $request->route('oportunidade')->slug ?? $request->route('slug');
        Log::info("TrackPageVisit: Rastreando visita para slug: " . $slug);
        
        $oportunidade = Oportunidade::where('slug', $slug)->first();
        
        if (!$oportunidade) {
            Log::warning("TrackPageVisit: Oportunidade não encontrada para slug: " . $slug);
            return;
        }

        Log::info("TrackPageVisit: Oportunidade encontrada ID: " . $oportunidade->id . ", chamando analyticsService");
        
        // Usar o serviço de analytics para rastrear a visita
        $visit = $this->analyticsService->trackVisit($request, $oportunidade);
        
        if ($visit) {
            Log::info("TrackPageVisit: Visita registrada com sucesso, ID: " . $visit->id);
        } else {
            Log::warning("TrackPageVisit: Visita não foi registrada");
        }
    }
}
